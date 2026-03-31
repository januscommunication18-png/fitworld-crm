<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\HelpdeskTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingSupportController extends Controller
{
    protected function getHost()
    {
        $host = Auth::user()->currentHost() ?? Auth::user()->host;

        if (!$host) {
            abort(403, 'No studio access. Please select a studio.');
        }

        return $host;
    }

    /**
     * Display listing of onboarding support requests for this studio.
     */
    public function index(Request $request)
    {
        $host = $this->getHost();
        $tab = $request->get('tab', 'all');
        $search = $request->get('search');

        $query = HelpdeskTicket::where('host_id', $host->id)
            ->where('source_type', HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT);

        // Apply tab filters
        switch ($tab) {
            case 'open':
                $query->where('status', HelpdeskTicket::STATUS_OPEN);
                break;
            case 'in_progress':
                $query->where('status', HelpdeskTicket::STATUS_IN_PROGRESS);
                break;
            case 'resolved':
                $query->where('status', HelpdeskTicket::STATUS_RESOLVED);
                break;
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Tab counts
        $counts = [
            'all' => HelpdeskTicket::where('host_id', $host->id)
                ->where('source_type', HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT)->count(),
            'open' => HelpdeskTicket::where('host_id', $host->id)
                ->where('source_type', HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT)
                ->where('status', HelpdeskTicket::STATUS_OPEN)->count(),
            'in_progress' => HelpdeskTicket::where('host_id', $host->id)
                ->where('source_type', HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT)
                ->where('status', HelpdeskTicket::STATUS_IN_PROGRESS)->count(),
            'resolved' => HelpdeskTicket::where('host_id', $host->id)
                ->where('source_type', HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT)
                ->where('status', HelpdeskTicket::STATUS_RESOLVED)->count(),
        ];

        return view('host.onboarding-support.index', compact('requests', 'tab', 'search', 'counts', 'host'));
    }

    /**
     * Display detail view of an onboarding support request.
     */
    public function show(HelpdeskTicket $ticket)
    {
        $host = $this->getHost();

        // Ensure this ticket belongs to this host and is an onboarding support ticket
        if ($ticket->host_id !== $host->id || $ticket->source_type !== HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT) {
            abort(404);
        }

        $ticket->load(['assignedUser', 'messages.user']);

        return view('host.onboarding-support.show', compact('ticket', 'host'));
    }

    /**
     * Display step detail for a support request.
     */
    public function step(HelpdeskTicket $ticket, int $step)
    {
        $host = $this->getHost();

        // Ensure this ticket belongs to this host
        if ($ticket->host_id !== $host->id || $ticket->source_type !== HelpdeskTicket::SOURCE_ONBOARDING_SUPPORT) {
            abort(404);
        }

        // Validate step number
        if ($step < 1 || $step > 5) {
            abort(404);
        }

        $stepInfo = $this->getStepInfo($host, $step);

        return view('host.onboarding-support.step', compact('ticket', 'step', 'stepInfo', 'host'));
    }

    /**
     * Get detailed information for a specific step.
     */
    private function getStepInfo($host, int $step): array
    {
        $owner = $host->owner;

        $steps = [
            1 => [
                'title' => 'Verify Email & Phone',
                'icon' => 'icon-[tabler--mail-check]',
                'description' => 'Email and phone verification status',
            ],
            2 => [
                'title' => 'Studio Information',
                'icon' => 'icon-[tabler--building-store]',
                'description' => 'Studio name, structure, and settings',
            ],
            3 => [
                'title' => 'Location Details',
                'icon' => 'icon-[tabler--map-pin]',
                'description' => 'Physical or virtual location setup',
            ],
            4 => [
                'title' => 'Team Members',
                'icon' => 'icon-[tabler--users]',
                'description' => 'Staff and team member invitations',
            ],
            5 => [
                'title' => 'Booking Page',
                'icon' => 'icon-[tabler--calendar-check]',
                'description' => 'Booking page settings and logo',
            ],
        ];

        $info = $steps[$step];
        $currentStep = $host->post_signup_step ?? 1;
        $isCompleted = $host->post_signup_completed_at !== null;

        $info['is_completed'] = $isCompleted || $step < $currentStep;
        $info['is_current'] = !$isCompleted && $step === $currentStep;
        $info['is_pending'] = !$isCompleted && $step > $currentStep;

        // Add step-specific data
        switch ($step) {
            case 1:
                $info['data'] = [
                    'email' => $owner?->email,
                    'email_verified' => $owner?->email_verified_at !== null,
                    'email_verified_at' => $owner?->email_verified_at,
                    'phone' => $host->owner_phone_number ? ($host->owner_phone_country_code . ' ' . $host->owner_phone_number) : null,
                    'phone_verified' => $host->owner_phone_verified ?? false,
                    'phone_verified_at' => $host->owner_phone_verified_at,
                ];
                break;

            case 2:
                $info['data'] = [
                    'studio_name' => $host->studio_name,
                    'studio_structure' => $host->studio_structure,
                    'subdomain' => $host->subdomain,
                    'studio_types' => $host->studio_types ?? [],
                    'studio_categories' => $host->studio_categories ?? [],
                    'default_language' => $host->default_language_app,
                    'default_currency' => $host->default_currency,
                    'cancellation_window' => $host->cancellation_window_hours,
                ];
                break;

            case 3:
                $locations = $host->locations ?? collect();
                $info['data'] = [
                    'locations' => $locations,
                    'has_location' => $locations->count() > 0,
                ];
                break;

            case 4:
                $staffMembers = $host->staffMembers ?? collect();
                $info['data'] = [
                    'staff_members' => $staffMembers,
                    'staff_count' => $staffMembers->count(),
                ];
                break;

            case 5:
                $info['data'] = [
                    'booking_page_status' => $host->booking_page_status ?? 'draft',
                    'logo_url' => $host->logo_url,
                    'booking_url' => "https://{$host->subdomain}.fitcrm.app",
                ];
                break;
        }

        return $info;
    }
}
