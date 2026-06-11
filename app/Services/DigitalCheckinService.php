<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClientQrCode;
use App\Models\ClassSession;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\MembershipCheckin;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Core, HTTP-agnostic logic for digital (QR) check-in. The web controller and
 * the future Api/V1 controller both depend on this — it returns plain arrays.
 */
class DigitalCheckinService
{
    public function __construct(
        protected BookingService $bookingService,
    ) {}

    /**
     * Map a scanned QR token to its client (scoped to the host). Marks the
     * token as used. Returns null when the token is unknown/disabled.
     */
    public function resolveByToken(Host $host, string $token): ?Client
    {
        $qr = ClientQrCode::active()
            ->where('host_id', $host->id)
            ->where('qr_token', $token)
            ->with('client')
            ->first();

        if (! $qr || ! $qr->client) {
            return null;
        }

        $qr->markUsed();

        return $qr->client;
    }

    /**
     * Build the client's check-in options for the given moment ("today"):
     * booked class sessions, booked service slots, and active open-access /
     * QR-enabled membership. Each option is annotated with eligibility — no
     * writes happen here.
     *
     * @return array<int,array<string,mixed>>
     */
    public function resolveEligibleOptions(Host $host, Client $client, Carbon $now): array
    {
        $options = [];

        // --- Booked class sessions & service slots starting today ---
        $bookings = Booking::forHost($host->id)
            ->forClient($client->id)
            ->confirmed()
            ->whereHasMorph('bookable', [ClassSession::class, \App\Models\ServiceSlot::class], function ($q) use ($now) {
                $q->whereDate('start_time', $now->toDateString());
            })
            ->with('bookable')
            ->get();

        foreach ($bookings as $booking) {
            $bookable = $booking->bookable;
            if (! $bookable) {
                continue;
            }

            $isClass = $bookable instanceof ClassSession;
            $state = $booking->selfCheckInState();
            $paid = $this->bookingIsPaid($booking);
            $already = ($state['reason'] ?? null) === 'already';

            if ($state['allowed']) {
                $inWindow = true;
                if ($paid) {
                    $eligible = true;
                    $reason = null;
                } else {
                    $eligible = false;
                    $reason = 'unpaid';
                }
            } else {
                $inWindow = false;
                $eligible = false;
                $reason = $state['reason'] ?? 'disabled';
            }

            $options[] = [
                'type' => $isClass ? 'class_booking' : 'service_booking',
                'ref_id' => $booking->id,
                'label' => $isClass
                    ? ($bookable->display_title ?? 'Class')
                    : ($bookable->servicePlan?->name ?? 'Service'),
                'subtitle' => $this->timeSubtitle($bookable),
                'starts_at' => $bookable->start_time?->toIso8601String(),
                'in_window' => $inWindow,
                'paid' => $paid,
                'already' => $already,
                'eligible' => $eligible,
                'reason' => $reason,
            ];
        }

        // --- Active open-access / QR membership (open gym) ---
        $membership = $client->activeCustomerMembership();
        if ($membership) {
            $membership->loadMissing('membershipPlan');
            $plan = $membership->membershipPlan;

            if ($plan && ($plan->isOpenAccess() || $plan->qr_checkin_enabled)) {
                $already = MembershipCheckin::where('client_id', $client->id)
                    ->where('membership_plan_id', $plan->id)
                    ->today()
                    ->whereNull('checked_out_at')
                    ->exists();

                $hasCredits = $membership->hasAvailableCredits();

                $eligible = ! $already && $hasCredits;
                $reason = $already ? 'already' : (! $hasCredits ? 'no_credits' : null);

                $options[] = [
                    'type' => 'membership',
                    'ref_id' => $membership->id,
                    'label' => 'Open Gym — ' . $plan->name,
                    'subtitle' => $plan->type === MembershipPlan::TYPE_CREDITS
                        ? 'Credits left: ' . (int) $membership->credits_remaining
                        : 'Unlimited access',
                    'starts_at' => null,
                    'in_window' => true,
                    'paid' => true,
                    'already' => $already,
                    'eligible' => $eligible,
                    'reason' => $reason,
                ];
            }
        }

        return $options;
    }

    /**
     * Perform the check-in for a chosen option. Re-resolves and re-validates the
     * target before writing (never trusts caller-supplied eligibility).
     *
     * @param  array<string,mixed>  $option  Must contain 'type' and 'ref_id'.
     * @return array<string,mixed>  ['success'=>bool,'message'=>string, ...]
     */
    public function checkIn(Host $host, Client $client, array $option, ?User $staff, bool $override = false): array
    {
        $type = $option['type'] ?? null;
        $refId = $option['ref_id'] ?? null;

        if ($type === 'membership') {
            return $this->checkInMembership($host, $client, (int) $refId, $staff, $override);
        }

        if (in_array($type, ['class_booking', 'service_booking'], true)) {
            return $this->checkInBooking($host, $client, (int) $refId, $staff, $override);
        }

        return ['success' => false, 'message' => 'Unknown check-in option.'];
    }

    private function checkInBooking(Host $host, Client $client, int $bookingId, ?User $staff, bool $override): array
    {
        $booking = Booking::forHost($host->id)
            ->forClient($client->id)
            ->with('bookable')
            ->find($bookingId);

        if (! $booking) {
            return ['success' => false, 'message' => 'Booking not found.'];
        }

        if ($booking->checked_in_at) {
            return ['success' => false, 'message' => 'Already checked in.', 'reason' => 'already'];
        }

        $state = $booking->selfCheckInState();
        $paid = $this->bookingIsPaid($booking);
        $blockUnpaid = (bool) $host->getPolicy('block_unpaid_checkin', true);

        if (! $override) {
            if (! $state['allowed']) {
                return [
                    'success' => false,
                    'message' => $this->reasonMessage($state['reason'] ?? 'disabled'),
                    'reason' => $state['reason'] ?? 'disabled',
                ];
            }
            if ($blockUnpaid && ! $paid) {
                return ['success' => false, 'message' => 'Payment is not complete.', 'reason' => 'unpaid'];
            }
        }

        $this->bookingService->checkIn($booking, $staff?->id, Booking::CHECKIN_STAFF);

        return [
            'success' => true,
            'message' => 'Checked in for ' . ($booking->bookable?->display_title
                ?? $booking->bookable?->servicePlan?->name
                ?? 'session') . '.',
            'checked_in_at' => now()->format('g:i A'),
        ];
    }

    private function checkInMembership(Host $host, Client $client, int $membershipId, ?User $staff, bool $override): array
    {
        $membership = CustomerMembership::where('host_id', $host->id)
            ->where('client_id', $client->id)
            ->where('id', $membershipId)
            ->with('membershipPlan')
            ->first();

        if (! $membership || ! $membership->is_active || $membership->is_expired) {
            return ['success' => false, 'message' => 'Membership is not active.', 'reason' => 'disabled'];
        }

        $plan = $membership->membershipPlan;

        $alreadyToday = MembershipCheckin::where('client_id', $client->id)
            ->where('membership_plan_id', $plan->id)
            ->today()
            ->whereNull('checked_out_at')
            ->exists();

        if ($alreadyToday) {
            return ['success' => false, 'message' => 'Already checked in today.', 'reason' => 'already'];
        }

        if (! $override && ! $membership->hasAvailableCredits()) {
            return ['success' => false, 'message' => 'No credits remaining.', 'reason' => 'no_credits'];
        }

        return DB::transaction(function () use ($host, $client, $membership, $plan, $staff) {
            MembershipCheckin::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'customer_membership_id' => $membership->id,
                'membership_plan_id' => $plan->id,
                'checked_in_at' => now(),
                'checked_in_by' => $staff?->id,
            ]);

            if ($plan->type === MembershipPlan::TYPE_CREDITS) {
                $membership->deductCredit();
            }

            return [
                'success' => true,
                'message' => 'Checked in for Open Gym — ' . $plan->name . '.',
                'checked_in_at' => now()->format('g:i A'),
            ];
        });
    }

    /**
     * Whether a booking should be treated as paid. There's no single flag, so
     * combine the signals (see plan): credit/comp methods, a completed Payment
     * row, or a confirmed booking with a recorded amount.
     */
    private function bookingIsPaid(Booking $booking): bool
    {
        if (in_array($booking->payment_method, [
            Booking::PAYMENT_MEMBERSHIP,
            Booking::PAYMENT_PACK,
            Booking::PAYMENT_COMP,
        ], true)) {
            return true;
        }

        if ($booking->payments()->completed()->exists()) {
            return true;
        }

        if ($booking->status === Booking::STATUS_CONFIRMED && (float) $booking->price_paid > 0) {
            return true;
        }

        return false;
    }

    private function timeSubtitle($bookable): string
    {
        $parts = [];
        if ($bookable->start_time) {
            $parts[] = $bookable->start_time->format('g:i A')
                . ($bookable->end_time ? ' – ' . $bookable->end_time->format('g:i A') : '');
        }
        if (($bookable->location?->name ?? null)) {
            $parts[] = $bookable->location->name;
        }

        return implode(' · ', $parts);
    }

    private function reasonMessage(string $reason): string
    {
        return match ($reason) {
            'too_early' => 'Check-in has not opened yet.',
            'too_late' => 'The check-in window has closed.',
            'already' => 'Already checked in.',
            'not_confirmed' => 'Booking is not confirmed.',
            'unpaid' => 'Payment is not complete.',
            'no_credits' => 'No credits remaining.',
            'disabled' => 'Check-in is not available.',
            'no_session_time' => 'Session has no scheduled time.',
            default => 'Not eligible for check-in.',
        };
    }
}
