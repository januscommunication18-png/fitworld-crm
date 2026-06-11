<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BookingResource;
use App\Http\Resources\Api\V1\ClientResource;
use App\Http\Resources\Api\V1\SpaceRentalDetailResource;
use App\Models\BillingCredit;
use App\Models\Booking;
use App\Models\ClassPassPurchase;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\ServicePlan;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Models\Transaction;
use App\Rules\ValidName;
use App\Services\BookingService;
use App\Services\ClassPassService;
use App\Services\InvoiceService;
use App\Services\MembershipService;
use App\Services\Schedule\SpaceRentalConflictChecker;
use App\Services\SpaceRentalService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Mobile "Add Booking" — creation endpoints for the three booking types
 * (class session, service slot, space rental) plus the option feeds that
 * power the form. Mirrors the host web walk-in and space-rental flows but
 * with the v1 mobile scope: single bookings, no promo codes / price
 * overrides / series. Host resolved by `studio.context`.
 */
class BookingCreateController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected SpaceRentalService $spaceRentalService,
        protected MembershipService $membershipService,
        protected ClassPassService $classPassService
    ) {}

    /**
     * Bookable pickers for the Add Booking form: upcoming class sessions,
     * available service slots and active rentable spaces. Gated by
     * `bookings.create`.
     */
    public function formOptions(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $sessions = ClassSession::where('host_id', $host->id)
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->where('start_time', '>=', now())
            ->with(['classPlan', 'primaryInstructor', 'location'])
            ->withCount(['bookings' => fn ($b) => $b->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_COMPLETED,
            ])])
            ->orderBy('start_time')
            ->limit(300)
            ->get()
            ->map(function (ClassSession $s) {
                $capacity = (int) $s->capacity;
                $booked = (int) ($s->bookings_count ?? 0);

                return [
                    'id' => $s->id,
                    'title' => $s->classPlan?->name ?? $s->title ?? 'Class',
                    'subtitle' => $this->joinParts([$s->primaryInstructor?->name, $s->location?->name]),
                    'start_time' => $s->start_time?->toIso8601String(),
                    'end_time' => $s->end_time?->toIso8601String(),
                    'capacity' => $capacity,
                    'booked' => $booked,
                    'spots_left' => $capacity > 0 ? max(0, $capacity - $booked) : null,
                    'price' => $s->getEffectivePrice(),
                    'class_plan_id' => $s->class_plan_id,
                ];
            })
            ->values();

        $slots = ServiceSlot::where('host_id', $host->id)
            ->where('status', ServiceSlot::STATUS_AVAILABLE)
            ->where('start_time', '>=', now())
            ->with(['servicePlan', 'instructor', 'location'])
            ->orderBy('start_time')
            ->limit(300)
            ->get()
            ->map(fn (ServiceSlot $slot) => [
                'id' => $slot->id,
                'title' => $slot->servicePlan?->name ?? $slot->title ?? 'Service',
                'subtitle' => $this->joinParts([$slot->instructor?->name, $slot->location?->name]),
                'start_time' => $slot->start_time?->toIso8601String(),
                'end_time' => $slot->end_time?->toIso8601String(),
                'price' => $slot->getEffectivePrice(),
                'service_plan_id' => $slot->service_plan_id,
            ])
            ->values();

        $currency = $host->default_currency ?? 'USD';
        $spaces = SpaceRentalConfig::where('host_id', $host->id)
            ->active()
            ->with(['location', 'room'])
            ->orderBy('name')
            ->get()
            ->map(fn (SpaceRentalConfig $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'subtitle' => $this->joinParts([$c->location?->name, $c->room?->name]),
                'hourly_rate' => $c->getHourlyRateForCurrency($currency),
                'deposit_amount' => $c->getDepositForCurrency($currency),
                'currency' => $currency,
                'minimum_hours' => $c->minimum_hours !== null ? (float) $c->minimum_hours : null,
                'maximum_hours' => $c->maximum_hours !== null ? (float) $c->maximum_hours : null,
            ])
            ->values();

        // Class/service types as the catalog lists them.
        $classPlans = ClassPlan::where('host_id', $host->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ClassPlan $p) => ['id' => $p->id, 'name' => $p->name])
            ->values();

        $servicePlans = ServicePlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (ServicePlan $p) => ['id' => $p->id, 'name' => $p->name])
            ->values();

        return response()->json(['data' => [
            'class_plans' => $classPlans,
            'service_plans' => $servicePlans,
            'class_sessions' => $sessions,
            'service_slots' => $slots,
            'spaces' => $spaces,
            'rental_purposes' => $this->kvList(SpaceRentalConfig::getPurposes()),
            'rental_statuses' => $this->kvList([
                SpaceRental::STATUS_DRAFT => 'Draft',
                SpaceRental::STATUS_PENDING => 'Pending',
                SpaceRental::STATUS_CONFIRMED => 'Confirmed',
            ]),
            'manual_methods' => $this->kvList([
                'cash' => 'Cash',
                'card' => 'Card',
                'check' => 'Check',
                'other' => 'Other',
            ]),
            'can_comp' => $request->user()->hasPermission('bookings.comp', $host),
            'currency' => $currency,
            // Phone country selector — same data the host web `x-phone-input` uses.
            'phone_countries' => array_values($host->operating_countries ?: [$host->country ?? 'US']),
            'phone_default_country' => $host->country ?? (($host->operating_countries ?: [])[0] ?? 'US'),
        ]]);
    }

    /**
     * Per-class-plan options for the Add Booking form: billing-period
     * discounts (for Series bookings) and the plan's recurring schedules
     * within the chosen period. Mirrors the web walk-in
     * `getClassPlanDefaults` + `getClassSchedules`.
     * Query: class_plan_id (required), months (1|3|6|9|12, default 1).
     */
    public function classOptions(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $validated = $request->validate([
            'class_plan_id' => 'required|exists:class_plans,id',
            'months' => 'nullable|integer|in:1,3,6,9,12',
        ]);

        $plan = ClassPlan::where('host_id', $host->id)
            ->findOrFail($validated['class_plan_id']);
        $months = (int) ($validated['months'] ?? 1);

        // Group the period's published sessions by recurring schedule.
        $sessions = ClassSession::where('host_id', $host->id)
            ->where('class_plan_id', $plan->id)
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->whereBetween('start_time', [now(), now()->addMonths($months)])
            ->select(['id', 'recurrence_parent_id', 'recurrence_rule', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time'])
            ->with(['primaryInstructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        $grouped = $sessions->groupBy(
            fn ($s) => $s->recurrence_parent_id ?? ($s->recurrence_rule ? $s->id : 'oneoff')
        );

        $parentIds = $grouped->keys()->filter(fn ($k) => is_numeric($k))->values()->toArray();
        $parents = ClassSession::whereIn('id', $parentIds)
            ->select(['id', 'recurrence_rule', 'primary_instructor_id', 'location_id', 'title', 'start_time', 'end_time'])
            ->with(['primaryInstructor:id,name', 'location:id,name'])
            ->get()
            ->keyBy('id');

        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $schedules = [];
        foreach ($grouped as $key => $groupSessions) {
            if ($groupSessions->isEmpty()) {
                continue;
            }

            $first = $groupSessions->first();
            $parent = is_numeric($key) ? ($parents->get($key) ?? $first) : $first;

            $label = 'One-off Sessions';
            if ($key !== 'oneoff' && $parent->recurrence_rule) {
                $parsed = app(\App\Services\Schedule\RecurrenceService::class)
                    ->parseRecurrenceRule($parent->recurrence_rule);
                if (! empty($parsed['days_of_week'])) {
                    $label = collect($parsed['days_of_week'])
                        ->map(fn ($d) => $dayNames[(int) $d] ?? $d)
                        ->implode(', ');
                }
            } elseif ($key !== 'oneoff') {
                $label = $parent->start_time->format('l');
            }

            $schedules[] = [
                'key' => (string) $key,
                'title' => $parent->title,
                'label' => $label,
                'time' => $parent->start_time->format('g:i A').' - '.$parent->end_time->format('g:i A'),
                'instructor' => $parent->primaryInstructor?->name,
                'location' => $parent->location?->name,
                'last_session_date' => $groupSessions->last()?->start_time->format('M d, Y'),
                'session_count' => $groupSessions->count(),
                'session_ids' => $groupSessions->pluck('id')->values(),
            ];
        }

        return response()->json(['data' => [
            'billing_discounts' => $plan->getBillingDiscountsForCurrency($host->default_currency ?? 'USD') ?: null,
            'registration_fee' => (float) ($plan->registration_fee ?? 0),
            'schedules' => $schedules,
        ]]);
    }

    /**
     * Per-service-plan options for the Add Booking form, mirroring the web
     * walk-in `getServicePlanDefaults` + `getServiceSchedules`. Same response
     * shape as `classOptions` (schedules carry `session_ids`).
     * Query: service_plan_id (required), months (1|3|6|9|12, default 1).
     */
    public function serviceOptions(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $validated = $request->validate([
            'service_plan_id' => 'required|exists:service_plans,id',
            'months' => 'nullable|integer|in:1,3,6,9,12',
        ]);

        $plan = ServicePlan::where('host_id', $host->id)
            ->findOrFail($validated['service_plan_id']);
        $months = (int) ($validated['months'] ?? 1);

        $slots = ServiceSlot::where('host_id', $host->id)
            ->where('service_plan_id', $plan->id)
            ->where('status', ServiceSlot::STATUS_AVAILABLE)
            ->whereBetween('start_time', [now(), now()->addMonths($months)])
            ->select(['id', 'title', 'recurrence_parent_id', 'recurrence_rule', 'instructor_id', 'location_id', 'start_time', 'end_time'])
            ->with(['instructor:id,name', 'location:id,name'])
            ->orderBy('start_time')
            ->get();

        $grouped = $slots->groupBy(
            fn ($s) => $s->recurrence_parent_id ?? ($s->recurrence_rule ? $s->id : 'oneoff')
        );

        $parentIds = $grouped->keys()->filter(fn ($k) => is_numeric($k))->values()->toArray();
        $parents = ServiceSlot::whereIn('id', $parentIds)
            ->select(['id', 'title', 'recurrence_rule', 'instructor_id', 'location_id', 'start_time', 'end_time'])
            ->with(['instructor:id,name', 'location:id,name'])
            ->get()
            ->keyBy('id');

        $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $schedules = [];
        foreach ($grouped as $key => $groupSlots) {
            if ($groupSlots->isEmpty()) {
                continue;
            }

            $first = $groupSlots->first();
            $parent = is_numeric($key) ? ($parents->get($key) ?? $first) : $first;

            $label = 'One-off Slots';
            if ($key !== 'oneoff' && $parent->recurrence_rule) {
                $parsed = app(\App\Services\Schedule\RecurrenceService::class)
                    ->parseRecurrenceRule($parent->recurrence_rule);
                if (! empty($parsed['days_of_week'])) {
                    $label = collect($parsed['days_of_week'])
                        ->map(fn ($d) => $dayNames[(int) $d] ?? $d)
                        ->implode(', ');
                }
            } elseif ($key !== 'oneoff') {
                $label = $parent->start_time->format('l');
            }

            $schedules[] = [
                'key' => (string) $key,
                'title' => $parent->title,
                'label' => $label,
                'time' => $parent->start_time->format('g:i A').' - '.$parent->end_time->format('g:i A'),
                'instructor' => $parent->instructor?->name,
                'location' => $parent->location?->name,
                'last_session_date' => $groupSlots->last()?->start_time->format('M d, Y'),
                'session_count' => $groupSlots->count(),
                'session_ids' => $groupSlots->pluck('id')->values(),
            ];
        }

        return response()->json(['data' => [
            'billing_discounts' => $plan->getBillingDiscountsForCurrency($host->default_currency ?? 'USD') ?: null,
            'registration_fee' => (float) ($plan->registration_fee ?? 0),
            'schedules' => $schedules,
        ]]);
    }

    /**
     * Payment options for a client: eligible membership, class packs and
     * billing credits. Mirrors the web walk-in `getPaymentMethods`.
     * Query: class_plan_id, session_id, source_type.
     */
    public function paymentOptions(Request $request, int $id): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $clientModel = Client::forHost($host->id)->findOrFail($id);
        $classPlanId = $request->get('class_plan_id');
        $classPlan = $classPlanId ? ClassPlan::find($classPlanId) : null;

        $membership = null;
        if ($classPlan) {
            $eligible = $this->membershipService->getEligibleMembershipForClass($clientModel, $classPlan);
            if ($eligible) {
                $membership = [
                    'id' => $eligible->id,
                    'name' => $eligible->membershipPlan->name,
                    'credits_remaining' => $eligible->credits_remaining,
                ];
            }
        }

        $sourceType = $classPlanId ? 'class_plan' : $request->get('source_type');
        $billingCredits = BillingCredit::where('host_id', $host->id)
            ->where('client_id', $clientModel->id)
            ->active()
            ->when($sourceType, fn ($q) => $q->where('source_type', $sourceType))
            ->get()
            ->map(fn ($credit) => [
                'id' => $credit->id,
                'source_name' => $credit->getSourceName(),
                'credit_remaining' => (float) $credit->credit_remaining,
                'end_date' => $credit->end_date?->toDateString(),
            ])
            ->values();

        $packs = $this->classPassService->getEligiblePasses($clientModel, $classPlanId ? (int) $classPlanId : null);

        $session = $request->get('session_id')
            ? ClassSession::where('host_id', $host->id)->find($request->get('session_id'))
            : null;
        foreach ($packs as &$pack) {
            $purchase = ClassPassPurchase::with('classPass')->find($pack['id']);
            $pack['credits_required'] = $session && $purchase?->classPass
                ? $purchase->classPass->calculateCreditsForSession($session)
                : ($purchase?->classPass?->default_credits_per_class ?? 1);
        }
        unset($pack);

        return response()->json(['data' => [
            'membership' => $membership,
            'packs' => $packs,
            'billing_credits' => $billingCredits,
            'manual' => true,
            'comp' => $request->user()->hasPermission('bookings.comp', $host),
        ]]);
    }

    /**
     * Quick-add a walk-in client with just name + contact. Unlike the web
     * walk-in (which silently reuses a matching client), duplicates are
     * rejected with a field-level validation error.
     */
    public function quickAddClient(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['required', 'string', 'max:50', new ValidName],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if (! empty($validated['email'])
            && Client::forHost($host->id)->where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'A client with this email already exists.',
                'errors' => ['email' => ['A client with this email already exists.']],
            ], 422);
        }

        if (! empty($validated['phone'])
            && Client::forHost($host->id)->where('phone', $validated['phone'])->exists()) {
            return response()->json([
                'message' => 'A client with this phone number already exists.',
                'errors' => ['phone' => ['A client with this phone number already exists.']],
            ], 422);
        }

        $client = Client::create([
            'host_id' => $host->id,
            'created_by_user_id' => $request->user()->id,
            'created_via' => 'mobile',
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => Client::STATUS_ACTIVE,
        ]);

        return response()->json([
            'data' => new ClientResource($client->load('tags')),
        ], 201);
    }

    /**
     * Book a client into a class session (mobile walk-in).
     */
    public function storeClassBooking(Request $request, int $id): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $session = ClassSession::where('host_id', $host->id)->findOrFail($id);

        if ($request->input('payment_method') === 'comp'
            && ! $request->user()->hasPermission('bookings.comp', $host)) {
            abort(403, 'You do not have permission to comp bookings.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_method' => 'required|in:membership,pack,manual,comp,billing_credit',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'pack_id' => 'nullable|required_if:payment_method,pack|exists:class_pass_purchases,id',
            'billing_credit_id' => 'nullable|integer|exists:billing_credits,id',
            'credits_to_deduct' => 'nullable|integer|min:1|max:99',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'booking_type' => 'nullable|in:single,period,trial',
            'series_session_ids' => 'nullable|string',
            'billing_period' => 'nullable|integer|in:1,3,6,9,12',
            'billing_discount_percent' => 'nullable|numeric|min:0',
            'include_registration_fee' => 'nullable|in:0,1',
        ]);

        $client = Client::forHost($host->id)->findOrFail($validated['client_id']);

        // The pack must belong to this client (validation only checks existence).
        if ($validated['payment_method'] === 'pack' && ! empty($validated['pack_id'])) {
            $ownsPack = ClassPassPurchase::where('id', $validated['pack_id'])
                ->where('host_id', $host->id)
                ->where('client_id', $client->id)
                ->exists();
            if (! $ownsPack) {
                return response()->json([
                    'message' => 'The selected class pack does not belong to this client.',
                ], 422);
            }
        }

        // Series bookings: block only when nothing new would be added.
        $bookingType = $validated['booking_type'] ?? 'single';
        if ($bookingType === 'period' && ! empty($validated['series_session_ids'])) {
            $sessionIds = array_filter(explode(',', $validated['series_session_ids']));
            $existingCount = Booking::where('client_id', $client->id)
                ->where('bookable_type', ClassSession::class)
                ->whereIn('bookable_id', $sessionIds)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->count();

            if ($existingCount >= count($sessionIds)) {
                return response()->json([
                    'message' => "{$client->full_name} is already booked into all sessions in this schedule.",
                ], 422);
            }
        }

        try {
            $booking = $this->bookingService->createWalkInClassBooking(
                host: $host,
                client: $client,
                session: $session,
                options: [
                    'payment_method' => $validated['payment_method'],
                    'manual_method' => $validated['manual_method'] ?? null,
                    'price_paid' => $validated['price_paid'] ?? null,
                    'class_pass_purchase_id' => $validated['pack_id'] ?? null,
                    'credits_to_deduct' => $validated['credits_to_deduct'] ?? null,
                    'check_in_now' => $validated['check_in_now'] ?? false,
                    'payment_notes' => $validated['notes'] ?? null,
                    'capacity_override' => true,
                    'send_confirmation_email' => false, // transaction email below carries the invoice
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $this->applyBillingCredit($host, $client, $booking, $validated, $session->getEffectivePrice() ?? 0);

        // Series (billing period) bookings: create the prepaid billing credit
        // and book the remaining sessions, mirroring the web walk-in flow.
        if ($bookingType === 'period') {
            if (! empty($validated['billing_period']) && ! empty($validated['billing_discount_percent'])) {
                $billingPeriod = (int) $validated['billing_period'];
                $totalAmount = (float) $validated['billing_discount_percent']; // total for the entire period
                $classPlan = $session->classPlan;
                $baseMonthly = (float) ($classPlan?->default_price ?? $session->getEffectivePrice() ?? 0);
                $monthlyRate = $billingPeriod > 0 ? $totalAmount / $billingPeriod : 0;
                $totalWithout = $baseMonthly * $billingPeriod;
                $discountPct = $totalWithout > 0 ? round((1 - $totalAmount / $totalWithout) * 100, 2) : 0;
                $includeRegFee = ($validated['include_registration_fee'] ?? '1') === '1';

                BillingCredit::create([
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'source_type' => 'class_plan',
                    'source_id' => $classPlan?->id ?? 0,
                    'booking_id' => $booking->id,
                    'billing_period' => $billingPeriod,
                    'discount_percent' => $discountPct,
                    'amount_paid' => $totalAmount,
                    'monthly_rate' => $monthlyRate,
                    'original_monthly_rate' => $baseMonthly,
                    'credit_remaining' => $totalAmount,
                    'registration_fee_paid' => $includeRegFee ? (float) ($classPlan?->registration_fee ?? 0) : 0,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths($billingPeriod)->toDateString(),
                    'status' => BillingCredit::STATUS_ACTIVE,
                    'created_by' => $request->user()->id,
                ]);
            }

            if (! empty($validated['series_session_ids'])) {
                $sessionIds = array_map('intval', array_filter(explode(',', $validated['series_session_ids'])));
                $sessionIds = array_values(array_diff($sessionIds, [(int) $session->id]));

                $otherSessions = ClassSession::where('host_id', $host->id)
                    ->whereIn('id', $sessionIds)
                    ->where('status', ClassSession::STATUS_PUBLISHED)
                    ->get();

                foreach ($otherSessions as $otherSession) {
                    $alreadyBooked = Booking::where('client_id', $client->id)
                        ->where('bookable_type', ClassSession::class)
                        ->where('bookable_id', $otherSession->id)
                        ->where('status', '!=', Booking::STATUS_CANCELLED)
                        ->exists();
                    if ($alreadyBooked) {
                        continue;
                    }

                    Booking::create([
                        'host_id' => $host->id,
                        'client_id' => $client->id,
                        'bookable_type' => ClassSession::class,
                        'bookable_id' => $otherSession->id,
                        'status' => Booking::STATUS_CONFIRMED,
                        'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                        'capacity_override' => true,
                        'created_by_user_id' => $request->user()->id,
                        'payment_method' => 'series',
                        'price_paid' => 0, // covered by the series payment
                        'booked_at' => now(),
                    ]);
                }
            }
        }

        $this->recordTransaction(
            $host,
            $client,
            $booking,
            $validated,
            type: Transaction::TYPE_CLASS_BOOKING,
            purchasableType: ClassSession::class,
            purchasableId: $session->id,
            itemName: $session->display_title,
            itemDatetime: $session->start_time?->format('M j, Y g:i A'),
            itemInstructor: $session->primaryInstructor?->name,
            itemLocation: $session->room?->location?->name ?? $session->location?->name,
        );

        return response()->json([
            'message' => "Booking confirmed for {$client->full_name}.",
            'data' => new BookingResource($booking->fresh(['client', 'bookable'])),
        ], 201);
    }

    /**
     * Book a client into a service slot (mobile walk-in).
     */
    public function storeServiceBooking(Request $request, int $id): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $slot = ServiceSlot::where('host_id', $host->id)->findOrFail($id);

        if ($request->input('payment_method') === 'comp'
            && ! $request->user()->hasPermission('bookings.comp', $host)) {
            abort(403, 'You do not have permission to comp bookings.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_method' => 'required|in:membership,pack,manual,comp,billing_credit',
            'manual_method' => 'required_if:payment_method,manual|in:cash,card,check,other',
            'price_paid' => 'nullable|numeric|min:0',
            'billing_credit_id' => 'nullable|integer|exists:billing_credits,id',
            'check_in_now' => 'boolean',
            'notes' => 'nullable|string|max:500',
            'booking_type' => 'nullable|in:single,period,trial',
            'series_slot_ids' => 'nullable|string',
            'billing_period' => 'nullable|integer|in:1,3,6,9,12',
            'billing_discount_percent' => 'nullable|numeric|min:0',
            'include_registration_fee' => 'nullable|in:0,1',
        ]);

        $client = Client::forHost($host->id)->findOrFail($validated['client_id']);

        // Series bookings: block only when nothing new would be added.
        $bookingType = $validated['booking_type'] ?? 'single';
        if ($bookingType === 'period' && ! empty($validated['series_slot_ids'])) {
            $slotIds = array_filter(explode(',', $validated['series_slot_ids']));
            $existingCount = Booking::where('client_id', $client->id)
                ->where('bookable_type', ServiceSlot::class)
                ->whereIn('bookable_id', $slotIds)
                ->where('status', '!=', Booking::STATUS_CANCELLED)
                ->count();

            if ($existingCount >= count($slotIds)) {
                return response()->json([
                    'message' => "{$client->full_name} is already booked into all slots in this schedule.",
                ], 422);
            }
        }

        try {
            $booking = $this->bookingService->createWalkInServiceBooking(
                host: $host,
                client: $client,
                slot: $slot,
                options: [
                    'payment_method' => $validated['payment_method'],
                    'manual_method' => $validated['manual_method'] ?? null,
                    'price_paid' => $validated['price_paid'] ?? null,
                    'check_in_now' => $validated['check_in_now'] ?? false,
                    'payment_notes' => $validated['notes'] ?? null,
                    'send_confirmation_email' => false, // transaction email below carries the invoice
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $this->applyBillingCredit($host, $client, $booking, $validated, $slot->getEffectivePrice() ?? 0);

        // Series (billing period) bookings: create the prepaid billing credit
        // and book the remaining slots, mirroring the web walk-in flow.
        if ($bookingType === 'period') {
            if (! empty($validated['billing_period']) && ! empty($validated['billing_discount_percent'])) {
                $billingPeriod = (int) $validated['billing_period'];
                $totalAmount = (float) $validated['billing_discount_percent']; // total for the entire period
                $servicePlan = $slot->servicePlan;
                $baseMonthly = (float) ($slot->getEffectivePrice() ?? 0);
                $monthlyRate = $billingPeriod > 0 ? $totalAmount / $billingPeriod : 0;
                $totalWithout = $baseMonthly * $billingPeriod;
                $discountPct = $totalWithout > 0 ? round((1 - $totalAmount / $totalWithout) * 100, 2) : 0;
                $includeRegFee = ($validated['include_registration_fee'] ?? '1') === '1';

                BillingCredit::create([
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'source_type' => 'service_plan',
                    'source_id' => $servicePlan?->id ?? 0,
                    'booking_id' => $booking->id,
                    'billing_period' => $billingPeriod,
                    'discount_percent' => $discountPct,
                    'amount_paid' => $totalAmount,
                    'monthly_rate' => $monthlyRate,
                    'original_monthly_rate' => $baseMonthly,
                    'credit_remaining' => $totalAmount,
                    'registration_fee_paid' => $includeRegFee ? (float) ($servicePlan?->registration_fee ?? 0) : 0,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths($billingPeriod)->toDateString(),
                    'status' => BillingCredit::STATUS_ACTIVE,
                    'created_by' => $request->user()->id,
                ]);
            }

            if (! empty($validated['series_slot_ids'])) {
                $slotIds = array_map('intval', array_filter(explode(',', $validated['series_slot_ids'])));
                $slotIds = array_values(array_diff($slotIds, [(int) $slot->id]));

                if (! empty($slotIds)) {
                    $otherSlots = ServiceSlot::where('host_id', $host->id)
                        ->whereIn('id', $slotIds)
                        ->where('status', ServiceSlot::STATUS_AVAILABLE)
                        ->get();

                    foreach ($otherSlots as $otherSlot) {
                        $alreadyBooked = Booking::where('client_id', $client->id)
                            ->where('bookable_type', ServiceSlot::class)
                            ->where('bookable_id', $otherSlot->id)
                            ->where('status', '!=', Booking::STATUS_CANCELLED)
                            ->exists();
                        if ($alreadyBooked) {
                            continue;
                        }

                        Booking::create([
                            'host_id' => $host->id,
                            'client_id' => $client->id,
                            'bookable_type' => ServiceSlot::class,
                            'bookable_id' => $otherSlot->id,
                            'status' => Booking::STATUS_CONFIRMED,
                            'booking_source' => Booking::SOURCE_INTERNAL_WALKIN,
                            'capacity_override' => true,
                            'created_by_user_id' => $request->user()->id,
                            'payment_method' => 'series',
                            'price_paid' => 0, // covered by the series payment
                            'booked_at' => now(),
                        ]);

                        $otherSlot->update(['status' => ServiceSlot::STATUS_BOOKED]);
                    }
                }
            }
        }

        $this->recordTransaction(
            $host,
            $client,
            $booking,
            $validated,
            type: Transaction::TYPE_SERVICE_BOOKING,
            purchasableType: ServiceSlot::class,
            purchasableId: $slot->id,
            itemName: $slot->servicePlan?->name ?? $slot->title ?? 'Service',
            itemDatetime: $slot->start_time?->format('M j, Y g:i A'),
            itemInstructor: $slot->instructor?->name,
            itemLocation: $slot->location?->name,
        );

        return response()->json([
            'message' => "Slot booked for {$client->full_name}.",
            'data' => new BookingResource($booking->fresh(['client', 'bookable'])),
        ], 201);
    }

    /**
     * Price preview for a space rental: rate × hours + tax, plus deposit.
     */
    public function spaceRentalQuote(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $validated = $request->validate([
            'space_rental_config_id' => 'required|exists:space_rental_configs,id',
            'hours' => 'required|numeric|min:0.25',
        ]);

        $config = SpaceRentalConfig::where('host_id', $host->id)
            ->findOrFail($validated['space_rental_config_id']);

        $pricing = $this->spaceRentalService->calculatePricing(
            $config,
            (float) $validated['hours'],
            $config->location,
            $host->default_currency
        );

        return response()->json(['data' => [
            'hourly_rate' => $pricing['hourly_rate'],
            'hours' => $pricing['hours'],
            'subtotal' => $pricing['subtotal'],
            'tax_amount' => $pricing['tax_amount'],
            'total' => $pricing['total'],
            'currency' => $pricing['currency'],
            'deposit_amount' => $config->getDepositForCurrency($host->default_currency) ?? 0,
        ]]);
    }

    /**
     * Create a space rental (existing client or external renter).
     */
    public function storeSpaceRental(Request $request): JsonResponse
    {
        $host = $request->attributes->get('currentHost');
        $this->authorizeCreate($request, $host);

        $data = $request->validate([
            'space_rental_config_id' => 'required|exists:space_rental_configs,id',
            'client_type' => 'required|in:existing,external',
            'client_id' => 'required_if:client_type,existing|nullable|exists:clients,id',
            'external_client_name' => 'required_if:client_type,external|nullable|string|max:255',
            'external_client_email' => 'nullable|email|max:255',
            'external_client_phone' => 'nullable|string|max:50',
            'external_client_company' => 'nullable|string|max:255',
            'purpose' => 'required|in:photo_shoot,video_production,workshop,training,other',
            'purpose_notes' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'internal_notes' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,confirmed',
        ]);

        $config = SpaceRentalConfig::where('host_id', $host->id)
            ->findOrFail($data['space_rental_config_id']);

        $startTime = Carbon::parse($data['date'].' '.$data['start_time']);
        $endTime = Carbon::parse($data['date'].' '.$data['end_time']);
        $hours = $startTime->diffInMinutes($endTime) / 60;

        if ($config->minimum_hours && $hours < $config->minimum_hours) {
            return response()->json([
                'message' => "Minimum booking is {$config->minimum_hours} hours.",
            ], 422);
        }
        if ($config->maximum_hours && $hours > $config->maximum_hours) {
            return response()->json([
                'message' => "Maximum booking is {$config->maximum_hours} hours.",
            ], 422);
        }

        $conflicts = $this->spaceRentalService->checkConflicts($config, $startTime, $endTime);
        if (! empty($conflicts)) {
            $message = app(SpaceRentalConflictChecker::class)->formatConflictMessage($conflicts);

            return response()->json(['message' => $message], 422);
        }

        $rentalData = [
            'space_rental_config_id' => $config->id,
            'purpose' => $data['purpose'],
            'purpose_notes' => $data['purpose_notes'] ?? null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours_booked' => $hours,
            'internal_notes' => $data['internal_notes'] ?? null,
            'status' => $data['status'] ?? SpaceRental::STATUS_CONFIRMED,
        ];

        if ($data['client_type'] === 'existing') {
            $client = Client::forHost($host->id)->findOrFail($data['client_id']);
            $rentalData['client_id'] = $client->id;
        } else {
            $rentalData['external_client_name'] = $data['external_client_name'];
            $rentalData['external_client_email'] = $data['external_client_email'] ?? null;
            $rentalData['external_client_phone'] = $data['external_client_phone'] ?? null;
            $rentalData['external_client_company'] = $data['external_client_company'] ?? null;
        }

        try {
            $rental = $this->spaceRentalService->createRental($rentalData, $request->user());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Space rental created.',
            'data' => new SpaceRentalDetailResource($rental->load([
                'config.location', 'config.room', 'client', 'createdBy',
            ])),
        ], 201);
    }

    private function authorizeCreate(Request $request, $host): void
    {
        abort_unless(
            $request->user()->hasPermission('bookings.create', $host),
            403,
            'You do not have permission to create bookings.'
        );
    }

    /**
     * Deduct from a billing credit when used as the payment method, mirroring
     * the web walk-in flow (BookingService has no billing_credit case).
     */
    private function applyBillingCredit($host, Client $client, Booking $booking, array $validated, float $originalPrice): void
    {
        if (($validated['payment_method'] ?? null) !== 'billing_credit' || empty($validated['billing_credit_id'])) {
            return;
        }

        $credit = BillingCredit::where('id', $validated['billing_credit_id'])
            ->where('host_id', $host->id)
            ->where('client_id', $client->id)
            ->first();

        if ($credit && $credit->isUsable()) {
            $credit->deduct($originalPrice);
            $booking->update(['billing_credit_id' => $credit->id]);
        }
    }

    /**
     * Record the paid transaction + invoice + confirmation email for a class
     * or service booking, mirroring the web walk-in flow.
     */
    private function recordTransaction(
        $host,
        Client $client,
        Booking $booking,
        array $validated,
        string $type,
        string $purchasableType,
        int $purchasableId,
        ?string $itemName,
        ?string $itemDatetime,
        ?string $itemInstructor,
        ?string $itemLocation
    ): void {
        $pricePaid = (float) ($validated['price_paid'] ?? $booking->price_paid ?? 0);

        $transaction = Transaction::create([
            'host_id' => $host->id,
            'client_id' => $client->id,
            'type' => $type,
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasableId,
            'booking_id' => $booking->id,
            'subtotal' => $pricePaid,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $pricePaid,
            'currency' => $host->default_currency ?? 'USD',
            'status' => Transaction::STATUS_PAID,
            'payment_method' => $validated['payment_method'] === 'comp'
                ? Transaction::METHOD_COMP
                : Transaction::METHOD_MANUAL,
            'manual_method' => $validated['manual_method'] ?? null,
            'paid_at' => now(),
            'metadata' => [
                'item_name' => $itemName,
                'item_datetime' => $itemDatetime,
                'item_instructor' => $itemInstructor,
                'item_location' => $itemLocation,
                'source' => 'mobile',
            ],
            'notes' => $validated['notes'] ?? null,
        ]);

        try {
            app(InvoiceService::class)->createFromTransaction($transaction);
        } catch (\Exception $e) {
            Log::warning('Failed to create invoice for mobile booking', ['error' => $e->getMessage()]);
        }

        try {
            app(TransactionService::class)->sendConfirmationEmail($transaction, $booking);
        } catch (\Exception $e) {
            Log::warning('Failed to send mobile booking confirmation email', ['error' => $e->getMessage()]);
        }
    }

    /** @return array<int, array{value: string, label: string}> */
    private function kvList(array $map): array
    {
        $out = [];
        foreach ($map as $value => $label) {
            $out[] = ['value' => (string) $value, 'label' => (string) $label];
        }

        return $out;
    }

    private function joinParts(array $parts): ?string
    {
        $clean = array_values(array_filter($parts, fn ($p) => $p !== null && $p !== ''));

        return $clean ? implode(' · ', $clean) : null;
    }
}
