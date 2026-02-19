<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\ClassPlan;
use App\Models\ClassRequest;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\HelpdeskTicket;
use App\Models\Host;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                    'status' => Client::STATUS_LEAD,
                    'lead_source' => Client::SOURCE_WEBSITE,
                    'source_url' => request()->headers->get('referer'),
                ]);
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

        return redirect()->route('subdomain.class-request.success', ['subdomain' => $host->subdomain]);
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
