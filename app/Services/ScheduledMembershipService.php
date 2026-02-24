<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledMembershipService
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Enroll a member into all upcoming scheduled class sessions
     * Called when a membership is activated
     */
    public function enrollMemberIntoScheduledClasses(CustomerMembership $membership): array
    {
        $results = [
            'enrolled' => [],
            'skipped' => [],
            'errors' => [],
        ];

        // Check if membership plan has scheduled classes
        $membershipPlan = $membership->membershipPlan;
        if (!$membershipPlan || !$membershipPlan->has_scheduled_class) {
            return $results;
        }

        // Get class plans linked to this membership
        $classPlanIds = $membershipPlan->classPlans()->pluck('class_plans.id')->toArray();
        if (empty($classPlanIds)) {
            return $results;
        }

        // Get all upcoming published class sessions for these class plans
        $sessions = ClassSession::where('host_id', $membership->host_id)
            ->whereIn('class_plan_id', $classPlanIds)
            ->published()
            ->upcoming()
            ->orderBy('start_time')
            ->get();

        foreach ($sessions as $session) {
            $result = $this->enrollMemberIntoSession($membership, $session);
            if ($result['success']) {
                $results['enrolled'][] = $result;
            } elseif ($result['skipped']) {
                $results['skipped'][] = $result;
            } else {
                $results['errors'][] = $result;
            }
        }

        return $results;
    }

    /**
     * Enroll all scheduled membership holders into a class session
     * Called when a class session is published
     */
    public function enrollScheduledMembersIntoSession(ClassSession $session): array
    {
        $results = [
            'enrolled' => [],
            'skipped' => [],
            'errors' => [],
        ];

        // Get all membership plans that have scheduled classes and include this class plan
        $membershipPlans = MembershipPlan::where('host_id', $session->host_id)
            ->where('has_scheduled_class', true)
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->whereHas('classPlans', function ($query) use ($session) {
                $query->where('class_plans.id', $session->class_plan_id);
            })
            ->get();

        if ($membershipPlans->isEmpty()) {
            return $results;
        }

        // Get all active memberships for these plans
        $memberships = CustomerMembership::where('host_id', $session->host_id)
            ->whereIn('membership_plan_id', $membershipPlans->pluck('id'))
            ->active()
            ->notExpired()
            ->with('client')
            ->get();

        foreach ($memberships as $membership) {
            $result = $this->enrollMemberIntoSession($membership, $session);
            if ($result['success']) {
                $results['enrolled'][] = $result;
            } elseif ($result['skipped']) {
                $results['skipped'][] = $result;
            } else {
                $results['errors'][] = $result;
            }
        }

        return $results;
    }

    /**
     * Enroll a single member into a single session
     */
    public function enrollMemberIntoSession(CustomerMembership $membership, ClassSession $session): array
    {
        $client = $membership->client;
        $host = $membership->host;

        // Check if client already has a booking for this session
        if ($this->bookingService->hasExistingBooking($client, $session)) {
            return [
                'success' => false,
                'skipped' => true,
                'reason' => 'Already booked',
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'session_id' => $session->id,
                'session_title' => $session->display_title,
            ];
        }

        // Check capacity
        $capacityCheck = $this->bookingService->validateCapacity($session);
        if (!$capacityCheck['available']) {
            return [
                'success' => false,
                'skipped' => true,
                'reason' => 'Session at capacity',
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'session_id' => $session->id,
                'session_title' => $session->display_title,
            ];
        }

        // Check if membership is still valid for this session date
        if ($membership->expires_at && $membership->expires_at->lt($session->start_time)) {
            return [
                'success' => false,
                'skipped' => true,
                'reason' => 'Membership expires before session',
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'session_id' => $session->id,
                'session_title' => $session->display_title,
            ];
        }

        try {
            // Create the booking
            $booking = $this->createScheduledBooking($host, $client, $session, $membership);

            return [
                'success' => true,
                'skipped' => false,
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'session_id' => $session->id,
                'session_title' => $session->display_title,
                'session_time' => $session->start_time->format('M j, Y g:i A'),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to auto-enroll member into scheduled class', [
                'membership_id' => $membership->id,
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'skipped' => false,
                'reason' => $e->getMessage(),
                'client_id' => $client->id,
                'client_name' => $client->full_name,
                'session_id' => $session->id,
                'session_title' => $session->display_title,
            ];
        }
    }

    /**
     * Create a booking for a scheduled membership
     */
    protected function createScheduledBooking(
        Host $host,
        Client $client,
        ClassSession $session,
        CustomerMembership $membership
    ): Booking {
        return DB::transaction(function () use ($host, $client, $session, $membership) {
            $booking = Booking::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'bookable_type' => ClassSession::class,
                'bookable_id' => $session->id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_ONLINE, // Auto-enrolled
                'intake_status' => Booking::INTAKE_NOT_REQUIRED,
                'payment_method' => Booking::PAYMENT_MEMBERSHIP,
                'customer_membership_id' => $membership->id,
                'price_paid' => 0,
                'booked_at' => now(),
            ]);

            // Deduct credit if membership uses credits
            if (!$membership->is_unlimited && $membership->credits_remaining > 0) {
                $membership->deductCredit();
            }

            return $booking;
        });
    }

    /**
     * Get upcoming scheduled sessions for a membership
     */
    public function getUpcomingScheduledSessions(CustomerMembership $membership): Collection
    {
        $membershipPlan = $membership->membershipPlan;
        if (!$membershipPlan || !$membershipPlan->has_scheduled_class) {
            return collect();
        }

        $classPlanIds = $membershipPlan->classPlans()->pluck('class_plans.id')->toArray();
        if (empty($classPlanIds)) {
            return collect();
        }

        return ClassSession::where('host_id', $membership->host_id)
            ->whereIn('class_plan_id', $classPlanIds)
            ->published()
            ->upcoming()
            ->with(['classPlan', 'primaryInstructor', 'location'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get members enrolled via scheduled membership for a session
     */
    public function getScheduledMembersForSession(ClassSession $session): Collection
    {
        return Booking::where('bookable_type', ClassSession::class)
            ->where('bookable_id', $session->id)
            ->whereNotNull('customer_membership_id')
            ->whereHas('customerMembership.membershipPlan', function ($query) {
                $query->where('has_scheduled_class', true);
            })
            ->with(['client', 'customerMembership.membershipPlan'])
            ->get();
    }

    /**
     * Remove scheduled enrollments when membership is cancelled/paused
     */
    public function removeScheduledEnrollments(CustomerMembership $membership, string $reason = 'Membership cancelled'): int
    {
        // Find all future bookings made via this membership
        $bookings = Booking::where('customer_membership_id', $membership->id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereHas('bookable', function ($query) {
                $query->where('start_time', '>', now());
            })
            ->get();

        $cancelledCount = 0;
        foreach ($bookings as $booking) {
            $booking->cancel($reason, 'Auto-cancelled due to membership status change', null);
            $cancelledCount++;
        }

        return $cancelledCount;
    }
}
