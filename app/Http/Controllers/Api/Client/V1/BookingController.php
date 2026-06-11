<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassPassPurchase;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private const MANUAL_METHOD_LABELS = [
        'venmo' => 'Venmo',
        'zelle' => 'Zelle',
        'cash_app' => 'Cash App',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Pay at Studio',
    ];

    /**
     * Upcoming bookable class sessions at the client's studio, with
     * availability and whether the signed-in client already booked each one.
     */
    public function classes(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $sessions = ClassSession::query()
            ->where('host_id', $client->host_id)
            ->published()
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(14))
            ->with(['classPlan', 'primaryInstructor', 'location'])
            ->withCount(['confirmedBookings'])
            ->orderBy('start_time')
            ->get();

        // The client's active bookings for these sessions, to mark "booked".
        $myBookings = $client->bookings()
            ->where('bookable_type', ClassSession::class)
            ->whereIn('bookable_id', $sessions->pluck('id'))
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->pluck('id', 'bookable_id');

        return response()->json(['data' => $sessions->map(function (ClassSession $s) use ($myBookings) {
            $capacity = $s->getEffectiveCapacity();
            $spotsLeft = $capacity > 0 ? max(0, $capacity - $s->confirmed_bookings_count) : null;

            return [
                'id' => $s->id,
                'name' => $s->title ?? $s->classPlan?->name ?? 'Class',
                'start_time' => $s->start_time->toIso8601String(),
                'end_time' => $s->end_time?->toIso8601String(),
                'instructor' => $s->primaryInstructor?->name,
                'location' => $s->location?->name,
                'price' => $s->getEffectivePrice(),
                'spots_left' => $spotsLeft,
                'booking_id' => $myBookings[$s->id] ?? null,
            ];
        })]);
    }

    /**
     * What the signed-in client would pay to book this session, and the
     * payment options available to them — mirrors the web booking flow
     * (membership credit, class pass credit, the studio's enabled manual
     * methods, with Pay-at-Studio as the fallback).
     */
    public function bookingOptions(Request $request, int $sessionId): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $session = $this->findBookableSession($client, $sessionId);
        if (! $session) {
            return response()->json(['message' => 'This class is no longer available.'], 422);
        }

        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';
        $price = $session->getEffectivePrice()
            ?? $session->classPlan?->getPriceForCurrency($currency)
            ?? 0.0;

        $options = [];

        if ($membership = $this->applicableMembership($client, $session)) {
            $options[] = [
                'id' => 'membership',
                'label' => $membership->membershipPlan?->name ?? 'Membership',
                'description' => $membership->credits_remaining === null
                    ? 'Included with your membership'
                    : "Uses 1 credit ({$membership->credits_remaining} left)",
                'price' => 0,
            ];
        }

        if ($pass = $this->applicablePass($client, $session)) {
            $credits = $pass->calculateCreditsForSession($session);
            $options[] = [
                'id' => 'pack',
                'label' => $pass->classPass?->name ?? 'Class pass',
                'description' => "Uses {$credits} credit".($credits === 1 ? '' : 's')
                    ." ({$pass->classes_remaining} of {$pass->classes_total} left)",
                'price' => 0,
            ];
        }

        // Membership-only session: no price, gated by linked plans. Covered
        // clients book through their membership/pass; uncovered clients get
        // the linked memberships offered for purchase — never a free booking.
        $gatedPlans = $price <= 0
            ? $session->membershipPlans()
                ->where('status', MembershipPlan::STATUS_ACTIVE)
                ->get()
            : collect();

        $offeredPlans = [];
        if ($gatedPlans->isNotEmpty() && empty($options)) {
            $currencyRef = $currency;
            $offeredPlans = $gatedPlans
                ->map(fn (MembershipPlan $p) => self::planOffer($p, $currencyRef))
                ->filter()
                ->values()
                ->all();
        }

        if ($price <= 0 && $gatedPlans->isEmpty()) {
            $options[] = [
                'id' => 'free',
                'label' => 'Free class',
                'description' => 'No payment needed',
                'price' => 0,
            ];
        } elseif ($price > 0) {
            $options = array_merge($options, self::manualPaymentOptions($host, $price));
        }

        [$full, $spotsLeft] = $this->capacityState($session);

        return response()->json(['data' => [
            'session' => [
                'id' => $session->id,
                'name' => $session->title ?? $session->classPlan?->name ?? 'Class',
                'start_time' => $session->start_time->toIso8601String(),
                'end_time' => $session->end_time?->toIso8601String(),
                'instructor' => $session->primaryInstructor?->name,
                'location' => $session->location?->name,
                'spots_left' => $spotsLeft,
                'full' => $full,
                'waitlist_available' => $full
                    && (bool) $host->getPolicy('enable_waitlist', false),
            ],
            'price' => (float) $price,
            'currency' => $currency,
            'options' => $options,
            'requires_membership' => ! empty($offeredPlans),
            'membership_plans' => $offeredPlans,
            // How a membership purchase can be paid (price filled in client-side).
            'purchase_methods' => empty($offeredPlans)
                ? []
                : self::manualPaymentOptions($host, 0),
        ]]);
    }

    /**
     * The studio's enabled manual payment methods, with Pay-at-Studio as the
     * fallback — the same set the web booking flow offers.
     */
    public static function manualPaymentOptions($host, float $price): array
    {
        $manualConfig = ($host->payment_settings ?? [])['manual_methods'] ?? [];
        $options = [];
        foreach (self::MANUAL_METHOD_LABELS as $key => $label) {
            if (! empty($manualConfig[$key]['enabled'])) {
                $options[] = [
                    'id' => $key,
                    'label' => $label,
                    'description' => $manualConfig[$key]['instructions'] ?? null,
                    'price' => $price,
                ];
            }
        }
        // In-app card payments aren't supported yet, so always leave a way
        // to pay: same fallback the web flow uses.
        if (empty($options)) {
            $options[] = [
                'id' => 'cash',
                'label' => 'Pay at Studio',
                'description' => 'Pay when you arrive',
                'price' => $price,
            ];
        }

        return $options;
    }

    /**
     * A membership plan offered for purchase, with prepay billing periods
     * (1 month up to a year; multi-month prepay discounts honored).
     */
    public static function planOffer(MembershipPlan $plan, string $currency): ?array
    {
        $base = $plan->getPriceForCurrency($currency) ?? 0;

        if ($plan->interval === MembershipPlan::INTERVAL_YEARLY) {
            $periods = [['months' => 12, 'total' => (float) $base, 'savings' => 0.0]];
        } else {
            $months = collect(['1', '3', '6', '9', '12'])
                ->merge(array_keys($plan->billing_discounts ?? []))
                ->map(fn ($m) => (int) $m)
                ->filter(fn ($m) => $m >= 1 && $m <= 12)
                ->unique()
                ->sort()
                ->values();

            $periods = $months->map(function (int $m) use ($plan, $currency, $base) {
                $discounted = $plan->getBillingPeriodTotalForCurrency($m, $currency);
                $total = $discounted > 0 ? $discounted : $base * $m;

                return [
                    'months' => $m,
                    'total' => (float) $total,
                    'savings' => (float) max(0, $base * $m - $total),
                ];
            })->all();
        }

        // A plan with no price at all isn't sellable from the app.
        if (collect($periods)->sum('total') <= 0) {
            return null;
        }

        $credits = $plan->type === MembershipPlan::TYPE_CREDITS
            ? "{$plan->credits_per_cycle} classes per cycle"
            : 'Unlimited classes';

        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'description' => $plan->description,
            'credits_label' => $credits,
            'periods' => $periods,
        ];
    }

    /**
     * Book the signed-in client into a class session using the chosen
     * payment option. Full classes become waitlisted bookings when the
     * studio allows a waitlist.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'class_session_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'in:membership,pack,free,'
                .implode(',', array_keys(self::MANUAL_METHOD_LABELS))],
        ]);

        $session = $this->findBookableSession($client, $validated['class_session_id']);
        if (! $session) {
            return response()->json(['message' => 'This class is no longer available.'], 422);
        }

        $host = $client->host;
        $method = $validated['payment_method'];
        $currency = $host->default_currency ?? 'USD';
        $price = $session->getEffectivePrice()
            ?? $session->classPlan?->getPriceForCurrency($currency)
            ?? 0.0;

        $result = DB::transaction(function () use ($client, $session, $host, $method, $price, $currency) {
            $alreadyBooked = $client->bookings()
                ->where('bookable_type', ClassSession::class)
                ->where('bookable_id', $session->id)
                ->whereNotIn('status', [Booking::STATUS_CANCELLED])
                ->lockForUpdate()
                ->exists();
            if ($alreadyBooked) {
                return ['error' => 'You already have a booking for this class.'];
            }

            $capacity = $session->getEffectiveCapacity();
            $isWaitlist = false;
            if ($capacity > 0
                && $session->confirmedBookings()->lockForUpdate()->count() >= $capacity) {
                if (! $host->getPolicy('enable_waitlist', false)) {
                    return ['error' => 'This class is full.'];
                }
                $isWaitlist = true;
            }

            // Resolve the chosen payment option against current state.
            $membership = null;
            $pass = null;
            $passCredits = 1;
            if ($method === 'membership') {
                $membership = $this->applicableMembership($client, $session);
                if (! $membership) {
                    return ['error' => 'Your membership does not cover this class.'];
                }
            } elseif ($method === 'pack') {
                $pass = $this->applicablePass($client, $session);
                if (! $pass) {
                    return ['error' => 'You have no class pass that covers this class.'];
                }
                $passCredits = $pass->calculateCreditsForSession($session);
                if ($pass->classes_remaining < $passCredits) {
                    return ['error' => 'Not enough credits left on your class pass.'];
                }
            } elseif ($method === 'free') {
                if ($price > 0) {
                    return ['error' => 'This class is not free — pick a payment method.'];
                }
                // Membership-gated sessions are never bookable as "free" —
                // covered clients book via membership/pack.
                $gated = $session->membershipPlans()
                    ->where('status', MembershipPlan::STATUS_ACTIVE)
                    ->exists();
                if ($gated) {
                    return ['error' => 'This class requires a membership.'];
                }
            }

            $covered = in_array($method, ['membership', 'pack', 'free'], true);

            $booking = Booking::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'bookable_type' => ClassSession::class,
                'bookable_id' => $session->id,
                'status' => $isWaitlist ? Booking::STATUS_WAITLISTED : Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_ONLINE,
                'intake_status' => Booking::INTAKE_NOT_REQUIRED,
                'payment_method' => match ($method) {
                    'membership' => Booking::PAYMENT_MEMBERSHIP,
                    'pack' => Booking::PAYMENT_PACK,
                    'free' => Booking::PAYMENT_COMP,
                    default => $method,
                },
                'price_paid' => $covered ? 0 : null, // manual: collected at the studio
                'booked_at' => now(),
            ]);

            if ($membership) {
                $membership->deductCredit();
            }
            if ($pass) {
                $pass->deductCredits($passCredits, $booking);
            }

            Transaction::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'booking_id' => $booking->id,
                'type' => Transaction::TYPE_CLASS_BOOKING,
                'subtotal' => $covered ? 0 : $price,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $covered ? 0 : $price,
                'currency' => $currency,
                'status' => $covered ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
                'payment_method' => match ($method) {
                    'membership' => Transaction::METHOD_MEMBERSHIP,
                    'pack' => Transaction::METHOD_PACK,
                    'free' => Transaction::METHOD_COMP,
                    default => Transaction::METHOD_MANUAL,
                },
                'manual_method' => $covered ? null : $method,
                'paid_at' => $covered ? now() : null,
                'metadata' => [
                    'class_session_id' => $session->id,
                    'is_waitlist' => $isWaitlist,
                    'source' => 'client_app',
                ],
            ]);

            return ['booking' => $booking];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        /** @var Booking $booking */
        $booking = $result['booking'];

        return response()->json(['data' => [
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]], 201);
    }

    /**
     * Cancel one of the signed-in client's own bookings.
     */
    public function cancel(Request $request, int $bookingId): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $booking = Booking::query()
            ->where('id', $bookingId)
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if (! $booking->canBeCancelled()) {
            return response()->json(['message' => 'This booking can no longer be cancelled.'], 422);
        }

        $booking->cancel('client_request', 'Cancelled via client app');

        return response()->json(['data' => [
            'booking_id' => $booking->id,
            'status' => $booking->status,
        ]]);
    }

    private function findBookableSession(Client $client, int $sessionId): ?ClassSession
    {
        return ClassSession::query()
            ->where('host_id', $client->host_id)
            ->published()
            ->where('start_time', '>=', now())
            ->with(['classPlan', 'primaryInstructor', 'location'])
            ->find($sessionId);
    }

    private function applicableMembership(Client $client, ClassSession $session): ?CustomerMembership
    {
        // Sessions scheduled under specific membership plans (no class plan)
        // are covered by holding one of those memberships.
        $linkedPlanIds = $session->membershipPlans()->pluck('membership_plans.id');

        return $client->customerMemberships()
            ->where('host_id', $client->host_id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->get()
            ->first(function (CustomerMembership $m) use ($session, $linkedPlanIds) {
                if ($m->is_expired || ! $m->hasAvailableCredits()) {
                    return false;
                }
                if ($linkedPlanIds->contains($m->membership_plan_id)) {
                    return true;
                }

                return $session->classPlan !== null
                    && $m->canUseForClassPlan($session->classPlan);
            });
    }

    private function applicablePass(Client $client, ClassSession $session): ?ClassPassPurchase
    {
        return $client->classPassPurchases()
            ->where('host_id', $client->host_id)
            ->usable()
            ->with('classPass')
            ->get()
            ->first(fn (ClassPassPurchase $p) => $p->canUseForClassSession($session)
                && $p->classes_remaining >= $p->calculateCreditsForSession($session));
    }

    /** @return array{0: bool, 1: ?int} [full, spotsLeft] */
    private function capacityState(ClassSession $session): array
    {
        $capacity = $session->getEffectiveCapacity();
        if ($capacity <= 0) {
            return [false, null];
        }
        $booked = $session->confirmedBookings()->count();

        return [$booked >= $capacity, max(0, $capacity - $booked)];
    }
}
