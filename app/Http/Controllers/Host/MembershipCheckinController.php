<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\MembershipCheckin;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipCheckinController extends Controller
{
    /**
     * Show the check-in screen for an open-access membership plan.
     */
    public function index(MembershipPlan $membershipPlan): View
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        if ($membershipPlan->host_id !== $host->id) {
            abort(403);
        }

        $todayCheckins = MembershipCheckin::forHost($host->id)
            ->forPlan($membershipPlan->id)
            ->today()
            ->with(['client', 'checkedInBy', 'location'])
            ->latest('checked_in_at')
            ->get();

        $locations = $host->locations()->orderBy('name')->get();

        $stats = [
            'today' => MembershipCheckin::forHost($host->id)->forPlan($membershipPlan->id)->today()->count(),
            'this_week' => MembershipCheckin::forHost($host->id)->forPlan($membershipPlan->id)
                ->where('checked_in_at', '>=', now()->startOfWeek())->count(),
            'this_month' => MembershipCheckin::forHost($host->id)->forPlan($membershipPlan->id)
                ->where('checked_in_at', '>=', now()->startOfMonth())->count(),
            'active_members' => CustomerMembership::where('membership_plan_id', $membershipPlan->id)
                ->where('status', 'active')->count(),
        ];

        return view('host.membership-checkin.index', compact(
            'membershipPlan', 'todayCheckins', 'locations', 'stats'
        ));
    }

    /**
     * Search members with active memberships for this plan.
     */
    public function searchMembers(Request $request, MembershipPlan $membershipPlan): JsonResponse
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Find clients with active memberships for this plan
        $activeMembershipIds = CustomerMembership::where('membership_plan_id', $membershipPlan->id)
            ->where('status', 'active')
            ->pluck('client_id');

        $clients = Client::forHost($host->id)
            ->whereIn('id', $activeMembershipIds)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $results = $clients->map(function ($client) use ($membershipPlan) {
            $membership = CustomerMembership::where('client_id', $client->id)
                ->where('membership_plan_id', $membershipPlan->id)
                ->where('status', 'active')
                ->first();

            // Check if already checked in today (and not checked out)
            $activeCheckin = MembershipCheckin::where('client_id', $client->id)
                ->where('membership_plan_id', $membershipPlan->id)
                ->today()
                ->whereNull('checked_out_at')
                ->first();

            return [
                'id' => $client->id,
                'full_name' => $client->full_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'initials' => $client->initials,
                'avatar_url' => $client->avatar_url ?? null,
                'customer_membership_id' => $membership?->id,
                'membership_type' => $membershipPlan->type,
                'credits_remaining' => $membership?->credits_remaining,
                'already_checked_in' => $activeCheckin !== null,
                'checkin_id' => $activeCheckin?->id,
            ];
        });

        return response()->json($results);
    }

    /**
     * Check in a member (staff or self via QR).
     */
    public function store(Request $request, MembershipPlan $membershipPlan): JsonResponse
    {
        $host = $membershipPlan->host;
        $isSelfCheckin = $request->boolean('self_checkin');

        // QR self check-in: look up by email
        if ($isSelfCheckin && $request->filled('email')) {
            $client = Client::forHost($host->id)->where('email', $request->email)->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email. Please check with the front desk.',
                ], 422);
            }

            $membership = CustomerMembership::where('client_id', $client->id)
                ->where('membership_plan_id', $membershipPlan->id)
                ->where('status', 'active')
                ->first();

            if (!$membership) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active membership found. Please check with the front desk.',
                ], 422);
            }

            $validated = [
                'client_id' => $client->id,
                'customer_membership_id' => $membership->id,
                'location_id' => null,
                'notes' => 'Self check-in via QR',
            ];
        } else {
            // Staff check-in
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'customer_membership_id' => 'required|exists:customer_memberships,id',
                'location_id' => 'nullable|exists:locations,id',
                'notes' => 'nullable|string|max:500',
            ]);
        }

        // Verify membership is active and belongs to this plan
        $membership = $membership ?? CustomerMembership::where('id', $validated['customer_membership_id'])
            ->where('membership_plan_id', $membershipPlan->id)
            ->where('client_id', $validated['client_id'])
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active membership found for this client.',
            ], 422);
        }

        // Check for credit-based plans
        if ($membershipPlan->type === MembershipPlan::TYPE_CREDITS) {
            if (!$membership->hasAvailableCredits()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No credits remaining for this billing period.',
                ], 422);
            }
        }

        // Check if already checked in today and not checked out
        $activeCheckin = MembershipCheckin::where('client_id', $validated['client_id'])
            ->where('membership_plan_id', $membershipPlan->id)
            ->today()
            ->whereNull('checked_out_at')
            ->first();

        if ($activeCheckin) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in today.',
            ], 422);
        }

        // Create check-in record
        $checkin = MembershipCheckin::create([
            'host_id' => $host->id,
            'client_id' => $validated['client_id'],
            'customer_membership_id' => $membership->id,
            'membership_plan_id' => $membershipPlan->id,
            'checked_in_at' => now(),
            'checked_in_by' => $isSelfCheckin ? null : auth()->id(),
            'location_id' => $validated['location_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Deduct credit if applicable
        if ($membershipPlan->type === MembershipPlan::TYPE_CREDITS) {
            $membership->deductCredit();
        }

        $checkin->load('client');

        return response()->json([
            'success' => true,
            'message' => $checkin->client->full_name . ' checked in successfully.',
            'checkin' => [
                'id' => $checkin->id,
                'client_name' => $checkin->client->full_name,
                'checked_in_at' => $checkin->checked_in_at->format('g:i A'),
                'credits_remaining' => $membership->fresh()->credits_remaining,
            ],
        ]);
    }

    /**
     * QR check-in page — members scan QR and enter their email to self check-in.
     */
    public function qrCheckin(MembershipPlan $membershipPlan): View
    {
        if (!$membershipPlan->qr_checkin_enabled) {
            abort(404);
        }

        $host = $membershipPlan->host;

        return view('host.membership-checkin.qr', compact('membershipPlan', 'host'));
    }

    /**
     * Check out a member.
     */
    public function checkOut(MembershipCheckin $membershipCheckin): JsonResponse
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        if ($membershipCheckin->host_id !== $host->id) {
            abort(403);
        }

        $membershipCheckin->update(['checked_out_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully.',
            'checked_out_at' => $membershipCheckin->checked_out_at->format('g:i A'),
        ]);
    }
}
