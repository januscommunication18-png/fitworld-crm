<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Mail\ClassRequestReceivedMail;
use App\Mail\ClassRequestTeamNotificationMail;
use App\Models\ClassPlan;
use App\Models\ClassRequest;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\Tag;
use App\Models\User;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClassRequestController extends Controller
{
    /**
     * Get the host from the request attributes (set by ResolveSubdomainHost middleware)
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Show the class request form
     */
    public function create(Request $request, string $subdomain = null, ?int $sessionId = null)
    {
        $host = $this->getHost($request);

        // Get active class plans
        $classPlans = ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedSession = null;
        $selectedClassPlan = null;

        // If a session ID is provided, load that session
        if ($sessionId) {
            $selectedSession = ClassSession::where('id', $sessionId)
                ->where('host_id', $host->id)
                ->with('classPlan')
                ->first();

            if ($selectedSession) {
                $selectedClassPlan = $selectedSession->classPlan;
            }
        }

        // Or pre-select a class plan via ?class_plan_id= query param (no session).
        if (!$selectedClassPlan && $request->filled('class_plan_id')) {
            $selectedClassPlan = ClassPlan::where('id', $request->get('class_plan_id'))
                ->where('host_id', $host->id)
                ->where('is_active', true)
                ->first();
        }

        // Get booking settings
        $bookingSettings = array_merge(
            Host::defaultBookingSettings(),
            $host->booking_settings ?? []
        );

        // Get logged-in member if authenticated
        $member = Auth::guard('member')->user();

        return view('subdomain.class-request', [
            'host' => $host,
            'classPlans' => $classPlans,
            'selectedSession' => $selectedSession,
            'selectedClassPlan' => $selectedClassPlan,
            'bookingSettings' => $bookingSettings,
            'member' => $member,
        ]);
    }

    /**
     * Store a new class request
     */
    public function store(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'class_plan_id' => 'required|exists:class_plans,id',
            'message' => 'nullable|string|max:2000',
            'waitlist_requested' => 'nullable|boolean',
        ]);

        // Verify class plan belongs to this host
        $classPlan = ClassPlan::where('id', $validated['class_plan_id'])
            ->where('host_id', $host->id)
            ->firstOrFail();

        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);
        $waitlistRequested = $validated['waitlist_requested'] ?? false;

        // Use database transaction for consistency
        DB::transaction(function () use ($host, $classPlan, $validated, $fullName, $waitlistRequested, &$classRequest, &$ticket, &$waitlistEntry, &$client) {
            // 1. Find or create Client as Lead
            $client = Client::where('host_id', $host->id)
                ->where('email', $validated['email'])
                ->first();

            if (!$client) {
                // Create new client as Lead
                $client = Client::create([
                    'host_id' => $host->id,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'status' => Client::STATUS_INACTIVE,
                    'lead_source' => Client::SOURCE_WEBSITE,
                    'source_url' => request()->headers->get('referer'),
                ]);

                // Auto-tag freshly-captured clients as "Lead" + "New Client" so the
                // studio can see at-a-glance where this contact came from. Tags are
                // looked up by slug against this host's tag list (seeded by
                // Tag::ensureDefaultsForHost); missing tags are silently skipped.
                Tag::ensureDefaultsForHost($host->id);
                $autoTagIds = Tag::forHost($host->id)
                    ->whereIn('slug', ['lead', 'new-client'])
                    ->pluck('id')
                    ->all();
                if (!empty($autoTagIds)) {
                    $client->tags()->syncWithoutDetaching($autoTagIds);
                    Tag::whereIn('id', $autoTagIds)->each(fn ($t) => $t->updateUsageCount());
                }
            } else {
                // Update existing client's phone if not set
                if (empty($client->phone) && !empty($validated['phone'])) {
                    $client->update(['phone' => $validated['phone']]);
                }
            }
            // 2. Create HelpDesk ticket (linked to client)
            $ticket = HelpdeskTicket::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'source_type' => HelpdeskTicket::SOURCE_BOOKING_REQUEST,
                'name' => $fullName,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'subject' => 'Class Request: ' . $classPlan->name,
                'message' => $validated['message'] ?? null,
                'status' => HelpdeskTicket::STATUS_OPEN,
                'source_url' => request()->headers->get('referer'),
            ]);

            // Add initial message if provided
            if (!empty($validated['message'])) {
                $ticket->addMessage($validated['message'], null, 'customer');
            }

            // 3. Create ClassRequest record (linked to helpdesk ticket and client)
            $classRequest = ClassRequest::create([
                'host_id' => $host->id,
                'class_plan_id' => $classPlan->id,
                'client_id' => $client->id,
                'helpdesk_ticket_id' => $ticket->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $validated['message'] ?? null,
                'waitlist_requested' => $waitlistRequested,
                'source' => 'web',
                'status' => ClassRequest::STATUS_OPEN,
            ]);

            // 4. If waitlist requested, also create WaitlistEntry
            if ($waitlistRequested) {
                $waitlistEntry = WaitlistEntry::create([
                    'host_id' => $host->id,
                    'class_request_id' => $classRequest->id,
                    'class_plan_id' => $classPlan->id,
                    'client_id' => $client->id,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'notes' => $validated['message'] ?? null,
                    'status' => WaitlistEntry::STATUS_WAITING,
                ]);
            }
        });

        // Fire notification emails outside the transaction so a send failure can't roll back the request.
        $this->sendClassRequestEmails($host, $classPlan, $classRequest);

        return redirect()->route('subdomain.class-request.success', ['subdomain' => $host->subdomain]);
    }

    /**
     * Send the customer confirmation and any configured team-member notifications.
     * Each send is recorded in EmailLog so it shows up in /settings/dev/email-logs.
     * Failures are logged but don't surface to the user — the request is already saved.
     */
    protected function sendClassRequestEmails(Host $host, ClassPlan $classPlan, ClassRequest $classRequest): void
    {
        $customerName = trim($classRequest->first_name . ' ' . $classRequest->last_name);
        $studioName = $host->studio_name ?? 'Our Studio';

        // 1) Customer confirmation
        if (!empty($classRequest->email)) {
            $bodyPreview = "Hi {$customerName}, we've received your request about {$classPlan->name}. A member of our team will follow up with you shortly.";
            $log = EmailLog::logEmail(
                recipientEmail: $classRequest->email,
                subject: "We got your request — {$classPlan->name}",
                bodyPreview: $bodyPreview,
                hostId: $host->id,
                recipientName: $customerName
            );

            try {
                Mail::to($classRequest->email)
                    ->send(new ClassRequestReceivedMail($classRequest, $classPlan, $host));
                $log->markAsSent();
            } catch (\Throwable $e) {
                $log->markAsFailed($e->getMessage());
                Log::warning('Failed to send class-request confirmation email', [
                    'class_request_id' => $classRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2) Team-member notifications (only those configured on the class plan)
        $notifyIds = $classPlan->notification_user_ids ?? [];
        if (empty($notifyIds)) {
            return;
        }

        $teamMembers = User::whereIn('id', $notifyIds)
            ->whereNotNull('email')
            ->get();

        foreach ($teamMembers as $member) {
            $memberName = $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
            $bodyPreview = "New class request for {$classPlan->name} from {$customerName} ({$classRequest->email}). " . ($classRequest->waitlist_requested ? 'Waitlist requested.' : '');
            $log = EmailLog::logEmail(
                recipientEmail: $member->email,
                subject: "New class request: {$classPlan->name} from {$customerName}",
                bodyPreview: $bodyPreview,
                hostId: $host->id,
                recipientName: $memberName
            );

            try {
                Mail::to($member->email)
                    ->send(new ClassRequestTeamNotificationMail($classRequest, $classPlan, $host, $member));
                $log->markAsSent();
            } catch (\Throwable $e) {
                $log->markAsFailed($e->getMessage());
                Log::warning('Failed to send class-request team notification email', [
                    'class_request_id' => $classRequest->id,
                    'team_member_id' => $member->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Show the success page
     */
    public function success(Request $request)
    {
        $host = $this->getHost($request);

        return view('subdomain.class-request-success', [
            'host' => $host,
        ]);
    }
}
