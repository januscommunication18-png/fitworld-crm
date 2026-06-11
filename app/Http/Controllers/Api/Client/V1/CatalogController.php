<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassPass;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\MembershipPlan;
use App\Models\RentalBooking;
use App\Models\RentalItem;
use App\Models\ServicePlan;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Models\Transaction;
use App\Services\SpaceRentalService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * What the studio sells to clients: membership plans (with prepay
     * billing periods) and class passes. The client-app counterpart of the
     * web catalog's memberships / class-passes tabs.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();
        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';

        $ownedPlanIds = $client->customerMemberships()
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->pluck('membership_plan_id');
        $pendingPlanIds = Transaction::query()
            ->where('client_id', $client->id)
            ->where('type', Transaction::TYPE_MEMBERSHIP_PURCHASE)
            ->where('status', Transaction::STATUS_PENDING)
            ->pluck('purchasable_id');

        $memberships = $host->membershipPlans()
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->where('visibility_public', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (MembershipPlan $plan) use ($currency, $ownedPlanIds, $pendingPlanIds) {
                $offer = BookingController::planOffer($plan, $currency);
                if ($offer === null) {
                    return null;
                }
                $offer['owned'] = $ownedPlanIds->contains($plan->id);
                $offer['pending'] = $pendingPlanIds->contains($plan->id);

                return $offer;
            })
            ->filter()
            ->values();

        $passes = $host->classPasses()
            ->where('status', ClassPass::STATUS_ACTIVE)
            ->where('visibility_public', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (ClassPass $pass) use ($currency) {
                $price = $pass->getPriceForCurrency($currency);
                if ($price === null || $price <= 0) {
                    return null;
                }

                return [
                    'id' => $pass->id,
                    'name' => $pass->name,
                    'description' => $pass->description,
                    'class_count' => $pass->class_count,
                    'price' => (float) $price,
                    'validity' => $pass->formatted_validity,
                ];
            })
            ->filter()
            ->values();

        $services = $host->servicePlans()
            ->where('is_active', true)
            ->where('is_visible_on_booking_page', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ServicePlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'duration_minutes' => $plan->duration_minutes,
                'category' => $plan->category,
                'price' => $plan->getPriceForCurrency($currency),
            ]);

        $myEventIds = EventAttendee::query()
            ->where('client_id', $client->id)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('event_id');
        $events = Event::query()
            ->forHost($client->host_id)
            ->published()
            ->upcoming()
            ->where('visibility', 'public')
            ->orderBy('start_datetime')
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->short_description,
                'start_time' => $event->start_datetime->toIso8601String(),
                'end_time' => $event->end_datetime?->toIso8601String(),
                'venue' => $event->event_type === 'online'
                    ? 'Online'.($event->online_platform ? " ({$event->online_platform})" : '')
                    : ($event->venue_name ?? $event->city),
                'spots_left' => $event->capacity === null
                    ? null
                    : max(0, $event->capacity - $event->registration_count),
                'full' => $event->capacity !== null
                    && $event->registration_count >= $event->capacity,
                'waitlist_enabled' => (bool) $event->waitlist_enabled,
                'registered' => $myEventIds->contains($event->id),
            ]);

        $spaces = $host->spaceRentalConfigs()
            ->where('is_active', true)
            ->with(['location', 'room'])
            ->orderBy('name')
            ->get()
            ->map(function (SpaceRentalConfig $config) use ($currency) {
                $rate = $config->getHourlyRateForCurrency($currency);
                if ($rate === null || $rate <= 0) {
                    return null;
                }

                return [
                    'id' => $config->id,
                    'name' => $config->name,
                    'description' => $config->description,
                    'space' => $config->room?->name ?? $config->location?->name,
                    'hourly_rate' => (float) $rate,
                    'deposit' => $config->getDepositForCurrency($currency),
                    'min_hours' => (int) ($config->minimum_hours ?? 1),
                    'max_hours' => (int) ($config->maximum_hours ?? 8),
                    'purposes' => $config->allowed_purposes ?? ['other'],
                ];
            })
            ->filter()
            ->values();

        $items = $host->rentalItems()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (RentalItem $item) use ($currency) {
                $price = $item->getPriceForCurrency($currency);
                if ($price === null || $price <= 0) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => $item->category,
                    'price' => (float) $price,
                    'deposit' => $item->getDepositForCurrency($currency),
                    'in_stock' => (int) $item->available_inventory,
                    'max_rental_days' => $item->max_rental_days,
                ];
            })
            ->filter()
            ->values();

        return response()->json(['data' => [
            'currency' => $currency,
            'memberships' => $memberships,
            'passes' => $passes,
            'services' => $services,
            'events' => $events,
            'rental_spaces' => $spaces,
            'rental_items' => $items,
            'purchase_methods' => BookingController::manualPaymentOptions($host, 0),
        ]]);
    }

    /**
     * Upcoming open slots for a service plan (next 60 days — service
     * schedules are sparser than class schedules).
     */
    public function serviceSlots(Request $request, int $planId): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();
        $currency = $client->host->default_currency ?? 'USD';

        $plan = ServicePlan::query()
            ->where('host_id', $client->host_id)
            ->where('is_active', true)
            ->find($planId);
        if (! $plan) {
            return response()->json(['message' => 'This service is no longer available.'], 422);
        }

        $slots = ServiceSlot::query()
            ->where('host_id', $client->host_id)
            ->where('service_plan_id', $plan->id)
            ->where('status', 'available')
            ->where('start_time', '>', now())
            ->where('start_time', '<=', now()->addDays(60))
            ->with(['instructor', 'location'])
            ->orderBy('start_time')
            ->get()
            ->map(fn (ServiceSlot $slot) => [
                'id' => $slot->id,
                'start_time' => $slot->start_time->toIso8601String(),
                'end_time' => $slot->end_time?->toIso8601String(),
                'instructor' => $slot->instructor?->name,
                'location' => $slot->location?->name,
                'price' => $slot->getEffectivePrice()
                    ?? $plan->getPriceForCurrency($currency)
                    ?? 0,
            ]);

        return response()->json(['data' => $slots]);
    }

    /**
     * Book a service slot, paying via one of the studio's manual methods.
     */
    public function bookService(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'service_slot_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string',
                'in:cash,venmo,zelle,paypal,cash_app,bank_transfer'],
        ]);

        $currency = $client->host->default_currency ?? 'USD';

        $result = DB::transaction(function () use ($client, $validated, $currency) {
            $slot = ServiceSlot::query()
                ->where('host_id', $client->host_id)
                ->where('status', 'available')
                ->where('start_time', '>', now())
                ->with('servicePlan')
                ->lockForUpdate()
                ->find($validated['service_slot_id']);
            if (! $slot) {
                return ['error' => 'This time slot is no longer available.'];
            }

            $price = $slot->getEffectivePrice()
                ?? $slot->servicePlan?->getPriceForCurrency($currency)
                ?? 0;
            $isFree = $price <= 0;

            $booking = Booking::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'bookable_type' => ServiceSlot::class,
                'bookable_id' => $slot->id,
                'status' => Booking::STATUS_CONFIRMED,
                'booking_source' => Booking::SOURCE_ONLINE,
                'intake_status' => Booking::INTAKE_NOT_REQUIRED,
                'payment_method' => $isFree
                    ? Booking::PAYMENT_COMP
                    : $validated['payment_method'],
                'price_paid' => $isFree ? 0 : null, // collected at the studio
                'booked_at' => now(),
            ]);

            $slot->update(['status' => 'booked']);

            Transaction::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'booking_id' => $booking->id,
                'type' => Transaction::TYPE_SERVICE_BOOKING,
                'subtotal' => $price,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $price,
                'currency' => $currency,
                'status' => $isFree ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
                'payment_method' => $isFree ? Transaction::METHOD_COMP : Transaction::METHOD_MANUAL,
                'manual_method' => $isFree ? null : $validated['payment_method'],
                'paid_at' => $isFree ? now() : null,
                'metadata' => ['service_slot_id' => $slot->id, 'source' => 'client_app'],
            ]);

            return ['booking' => $booking];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['data' => [
            'booking_id' => $result['booking']->id,
            'status' => $result['booking']->status,
        ]], 201);
    }

    /**
     * Register the signed-in client for an event (waitlist when full and
     * the event allows it). Events carry no price — registration is free.
     */
    public function registerEvent(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'event_id' => ['required', 'integer'],
        ]);

        $result = DB::transaction(function () use ($client, $validated) {
            $event = Event::query()
                ->forHost($client->host_id)
                ->published()
                ->upcoming()
                ->lockForUpdate()
                ->find($validated['event_id']);
            if (! $event) {
                return ['error' => 'This event is no longer open for registration.'];
            }

            $already = EventAttendee::query()
                ->where('event_id', $event->id)
                ->where('client_id', $client->id)
                ->whereNotIn('status', ['cancelled'])
                ->exists();
            if ($already) {
                return ['error' => 'You are already registered for this event.'];
            }

            $full = $event->capacity !== null
                && $event->registration_count >= $event->capacity;

            if ($full && ! $event->waitlist_enabled) {
                return ['error' => 'This event is full.'];
            }

            if ($full) {
                EventAttendee::create([
                    'event_id' => $event->id,
                    'client_id' => $client->id,
                    'status' => 'waitlisted',
                    'waitlist_position' => $event->waitlist_count + 1,
                    'waitlist_joined_at' => now(),
                ]);
                $event->increment('waitlist_count');

                return ['status' => 'waitlisted'];
            }

            EventAttendee::create([
                'event_id' => $event->id,
                'client_id' => $client->id,
                'status' => 'registered',
                'registered_at' => now(),
            ]);
            $event->increment('registration_count');

            return ['status' => 'registered'];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        return response()->json(['data' => ['status' => $result['status']]], 201);
    }

    /**
     * Request a space rental: pending until the studio confirms (and
     * collects payment via the chosen manual method).
     */
    public function rentSpace(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'space_rental_config_id' => ['required', 'integer'],
            'start_time' => ['required', 'date', 'after:now'],
            'hours' => ['required', 'numeric', 'min:0.5', 'max:24'],
            'purpose' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string',
                'in:cash,venmo,zelle,paypal,cash_app,bank_transfer'],
        ]);

        $config = SpaceRentalConfig::query()
            ->where('host_id', $client->host_id)
            ->where('is_active', true)
            ->with('location')
            ->find($validated['space_rental_config_id']);
        if (! $config) {
            return response()->json(['message' => 'This space is no longer available.'], 422);
        }

        $hours = (float) $validated['hours'];
        $minHours = (float) ($config->minimum_hours ?? 1);
        $maxHours = (float) ($config->maximum_hours ?? 24);
        if ($hours < $minHours || $hours > $maxHours) {
            return response()->json(['message' =>
                "Rentals must be between {$minHours} and {$maxHours} hours."], 422);
        }

        $start = Carbon::parse($validated['start_time']);
        $end = $start->copy()->addMinutes((int) round($hours * 60));

        $overlaps = SpaceRental::query()
            ->where('space_rental_config_id', $config->id)
            ->whereIn('status', [
                SpaceRental::STATUS_PENDING, 'confirmed', 'in_progress',
            ])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
        if ($overlaps) {
            return response()->json(['message' =>
                'That time is already booked — try another slot.'], 422);
        }

        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';
        $pricing = app(SpaceRentalService::class)
            ->calculatePricing($config, $hours, $config->location, $currency);
        $deposit = $config->getDepositForCurrency($currency) ?? 0;

        DB::transaction(function () use ($client, $config, $validated, $start, $end, $hours, $pricing, $deposit, $currency) {
            $rental = SpaceRental::create([
                'host_id' => $client->host_id,
                'space_rental_config_id' => $config->id,
                'client_id' => $client->id,
                'purpose' => $validated['purpose'],
                'start_time' => $start,
                'end_time' => $end,
                'hourly_rate' => $pricing['hourly_rate'],
                'hours_booked' => $hours,
                'subtotal' => $pricing['subtotal'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total'],
                'deposit_amount' => $deposit,
                'currency' => $currency,
                'status' => SpaceRental::STATUS_PENDING,
                'deposit_status' => $deposit > 0
                    ? SpaceRental::DEPOSIT_PENDING
                    : SpaceRental::DEPOSIT_NOT_REQUIRED,
            ]);

            Transaction::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'type' => Transaction::TYPE_RENTAL,
                'purchasable_type' => SpaceRental::class,
                'purchasable_id' => $rental->id,
                'subtotal' => $pricing['subtotal'],
                'tax_amount' => $pricing['tax_amount'],
                'discount_amount' => 0,
                'total_amount' => $pricing['total'],
                'currency' => $currency,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => Transaction::METHOD_MANUAL,
                'manual_method' => $validated['payment_method'],
                'metadata' => ['space_rental_id' => $rental->id, 'source' => 'client_app'],
            ]);
        });

        return response()->json(['data' => [
            'status' => 'pending',
            'total' => (float) $pricing['total'],
            'currency' => $currency,
        ]], 201);
    }

    /**
     * Request an item rental for a date: pending until the studio prepares
     * it and collects payment.
     */
    public function rentItem(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'rental_item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'rental_date' => ['required', 'date', 'after_or_equal:today'],
            'payment_method' => ['required', 'string',
                'in:cash,venmo,zelle,paypal,cash_app,bank_transfer'],
        ]);

        $item = RentalItem::query()
            ->where('host_id', $client->host_id)
            ->where('is_active', true)
            ->find($validated['rental_item_id']);
        if (! $item) {
            return response()->json(['message' => 'This item is no longer available.'], 422);
        }

        $date = Carbon::parse($validated['rental_date'])->startOfDay();
        $quantity = (int) $validated['quantity'];
        $dueDate = $date->copy()->addDays($item->max_rental_days ?? 1);

        if (! $item->isAvailableForDateRange($date, $dueDate, $quantity)) {
            return response()->json(['message' =>
                'Not enough of this item is available for that date.'], 422);
        }

        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';
        $unitPrice = $item->getPriceForCurrency($currency) ?? 0;
        $total = $unitPrice * $quantity;
        $deposit = ($item->getDepositForCurrency($currency) ?? 0) * $quantity;

        $rental = null;
        DB::transaction(function () use (
            $client, $item, $quantity, $unitPrice, $total, $deposit,
            $currency, $date, $dueDate, $validated, &$rental
        ) {
            $rental = RentalBooking::create([
                'host_id' => $client->host_id,
                'rental_item_id' => $item->id,
                'client_id' => $client->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $total,
                'deposit_amount' => $deposit,
                'currency' => $currency,
                'rental_date' => $date,
                'due_date' => $dueDate,
                'fulfillment_status' => 'pending',
            ]);

            $transaction = Transaction::create([
                'host_id' => $client->host_id,
                'client_id' => $client->id,
                'type' => Transaction::TYPE_RENTAL,
                'purchasable_type' => RentalBooking::class,
                'purchasable_id' => $rental->id,
                'subtotal' => $total,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $total,
                'currency' => $currency,
                'status' => Transaction::STATUS_PENDING,
                'payment_method' => Transaction::METHOD_MANUAL,
                'manual_method' => $validated['payment_method'],
                'metadata' => ['rental_booking_id' => $rental->id, 'source' => 'client_app'],
            ]);

            $rental->update(['transaction_id' => $transaction->id]);
        });

        return response()->json(['data' => [
            'status' => 'pending',
            'total' => (float) $total,
            'currency' => $currency,
            'due_date' => $dueDate->toDateString(),
        ]], 201);
    }

    /**
     * Buy a class pass. Manual payments stay pending until the studio
     * confirms; the pass activates then (TransactionService). Mirrors the
     * membership purchase flow.
     */
    public function purchasePass(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'class_pass_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string',
                'in:cash,venmo,zelle,paypal,cash_app,bank_transfer'],
        ]);

        $pass = ClassPass::query()
            ->where('host_id', $client->host_id)
            ->where('status', ClassPass::STATUS_ACTIVE)
            ->find($validated['class_pass_id']);

        if (! $pass) {
            return response()->json(['message' => 'This class pass is no longer available.'], 422);
        }

        $pendingPurchase = Transaction::query()
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->where('type', Transaction::TYPE_CLASS_PACK_PURCHASE)
            ->where('purchasable_id', $pass->id)
            ->where('status', Transaction::STATUS_PENDING)
            ->exists();
        if ($pendingPurchase) {
            return response()->json(['message' =>
                'You already requested this pass — pay at the studio to activate it.'], 422);
        }

        $host = $client->host;
        $currency = $host->default_currency ?? 'USD';
        $total = $pass->getPriceForCurrency($currency) ?? 0;
        $isFree = $total <= 0;

        $transaction = Transaction::create([
            'host_id' => $client->host_id,
            'client_id' => $client->id,
            'type' => Transaction::TYPE_CLASS_PACK_PURCHASE,
            'purchasable_type' => ClassPass::class,
            'purchasable_id' => $pass->id,
            'subtotal' => $total,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $total,
            'currency' => $currency,
            'status' => $isFree ? Transaction::STATUS_PAID : Transaction::STATUS_PENDING,
            'payment_method' => $isFree ? Transaction::METHOD_COMP : Transaction::METHOD_MANUAL,
            'manual_method' => $isFree ? null : $validated['payment_method'],
            'paid_at' => $isFree ? now() : null,
            'metadata' => ['source' => 'client_app'],
        ]);

        if ($isFree) {
            app(TransactionService::class)->activateClassPackPurchase($transaction);
        }

        return response()->json(['data' => [
            'status' => $isFree ? 'active' : 'pending',
            'total' => (float) $total,
            'currency' => $currency,
            'pass' => $pass->name,
        ]], 201);
    }
}
