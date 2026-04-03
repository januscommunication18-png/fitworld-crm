<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Services\Schedule\RecurrenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MembershipAccessController extends Controller
{
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Show membership welcome page with schedule selection
     */
    public function show(Request $request, string $subdomain, string $accessToken)
    {
        $host = $this->getHost($request);

        $membership = CustomerMembership::where('access_token', $accessToken)
            ->where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with(['client', 'membershipPlan'])
            ->firstOrFail();

        $client = $membership->client;
        $membershipPlan = $membership->membershipPlan;

        // Get linked schedules (parent sessions linked to this membership plan)
        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $recurrenceService = app(RecurrenceService::class);

        $parents = ClassSession::where('host_id', $host->id)
            ->whereNull('class_plan_id')
            ->whereHas('membershipPlans', function ($q) use ($membershipPlan) {
                $q->where('membership_plans.id', $membershipPlan->id);
            })
            ->where(function ($q) {
                $q->whereNotNull('recurrence_rule')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('recurrence_parent_id')->whereNull('recurrence_rule');
                  });
            })
            ->with(['primaryInstructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        $schedules = $parents->map(function ($parent) use ($dayNames, $recurrenceService) {
            $childCount = ClassSession::where('recurrence_parent_id', $parent->id)
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>=', now())
                ->count();

            $totalCount = $childCount + ($parent->start_time->isFuture() && $parent->status === ClassSession::STATUS_PUBLISHED ? 1 : 0);

            $dayLabel = $parent->start_time->format('l');
            if ($parent->recurrence_rule) {
                $parsed = $recurrenceService->parseRecurrenceRule($parent->recurrence_rule);
                if (!empty($parsed['days_of_week'])) {
                    $dayLabel = collect($parsed['days_of_week'])
                        ->map(fn($d) => $dayNames[(int) $d] ?? $d)
                        ->implode(', ');
                }
            }

            return (object) [
                'id' => $parent->id,
                'title' => $parent->title ?? 'Untitled',
                'days' => $dayLabel,
                'time' => $parent->start_time->format('g:i A') . ' - ' . $parent->end_time->format('g:i A'),
                'instructor' => $parent->primaryInstructor?->name ?? 'TBD',
                'location' => $parent->location?->name ?? '—',
                'session_count' => $totalCount,
                'is_recurring' => (bool) $parent->recurrence_rule,
            ];
        });

        // Check which schedules are already enrolled
        $enrolledSessionIds = Booking::where('customer_membership_id', $membership->id)
            ->where('bookable_type', ClassSession::class)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->pluck('bookable_id')
            ->toArray();

        return view('subdomain.membership-access', [
            'host' => $host,
            'membership' => $membership,
            'client' => $client,
            'membershipPlan' => $membershipPlan,
            'schedules' => $schedules,
            'enrolledSessionIds' => $enrolledSessionIds,
            'token' => $accessToken,
        ]);
    }

    /**
     * Process schedule selection
     */
    public function selectSchedules(Request $request, string $subdomain, string $accessToken)
    {
        $host = $this->getHost($request);

        $membership = CustomerMembership::where('access_token', $accessToken)
            ->where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with(['client', 'membershipPlan'])
            ->firstOrFail();

        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'integer|exists:class_sessions,id',
        ]);

        $client = $membership->client;
        $endDate = $membership->expires_at;
        $enrolledCount = 0;

        foreach ($validated['schedule_ids'] as $parentId) {
            // Get all upcoming published sessions for this parent schedule
            $sessions = ClassSession::where('host_id', $host->id)
                ->where(function ($q) use ($parentId) {
                    $q->where('id', $parentId)
                      ->orWhere('recurrence_parent_id', $parentId);
                })
                ->where('status', ClassSession::STATUS_PUBLISHED)
                ->where('start_time', '>=', now())
                ->when($endDate, fn($q) => $q->where('start_time', '<=', $endDate))
                ->get();

            foreach ($sessions as $session) {
                // Skip if already booked
                $exists = Booking::where('client_id', $client->id)
                    ->where('bookable_type', ClassSession::class)
                    ->where('bookable_id', $session->id)
                    ->whereNotIn('status', [Booking::STATUS_CANCELLED])
                    ->exists();

                if ($exists) continue;

                try {
                    Booking::create([
                        'host_id' => $host->id,
                        'client_id' => $client->id,
                        'bookable_type' => ClassSession::class,
                        'bookable_id' => $session->id,
                        'status' => Booking::STATUS_CONFIRMED,
                        'booking_source' => Booking::SOURCE_ONLINE,
                        'intake_status' => Booking::INTAKE_NOT_REQUIRED,
                        'payment_method' => Booking::PAYMENT_MEMBERSHIP,
                        'customer_membership_id' => $membership->id,
                        'price_paid' => 0,
                        'booked_at' => now(),
                    ]);
                    $enrolledCount++;
                } catch (\Exception $e) {
                    Log::warning('Schedule enrollment failed', [
                        'session_id' => $session->id,
                        'client_id' => $client->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('subdomain.membership-confirmed', [
            'subdomain' => $host->subdomain,
            'accessToken' => $accessToken,
        ])->with('enrolled_count', $enrolledCount);
    }

    /**
     * Show confirmation/thank you page
     */
    public function confirmed(Request $request, string $subdomain, string $accessToken)
    {
        $host = $this->getHost($request);

        $membership = CustomerMembership::where('access_token', $accessToken)
            ->where('host_id', $host->id)
            ->with(['client', 'membershipPlan'])
            ->firstOrFail();

        $enrolledCount = session('enrolled_count', 0);

        return view('subdomain.membership-confirmed', [
            'host' => $host,
            'membership' => $membership,
            'client' => $membership->client,
            'membershipPlan' => $membership->membershipPlan,
            'enrolledCount' => $enrolledCount,
            'token' => $accessToken,
        ]);
    }
}
