<?php

namespace App\Services;

use App\Mail\IntakeFormRequestMail;
use App\Mail\TransactionConfirmationMail;
use App\Models\Booking;
use App\Models\ClassPack;
use App\Models\ClassPackPurchase;
use App\Models\ClassPass;
use App\Models\ClassPassPurchase;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Event;
use App\Models\Host;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MembershipPlan;
use App\Models\QuestionnaireAttachment;
use App\Models\QuestionnaireResponse;
use App\Models\ServiceSlot;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionService
{
    /**
     * Create a transaction from booking flow state
     */
    public function createFromBookingFlow(
        Host $host,
        Client $client,
        array $selectedItem,
        string $paymentMethod,
        ?string $manualMethod = null
    ): Transaction {
        $type = $this->determineTransactionType($selectedItem['type']);
        $purchasableModel = $this->getPurchasableModel($selectedItem);

        $subtotal = (float) ($selectedItem['price'] ?? 0);
        $taxAmount = 0; // TODO: Calculate tax if configured
        $discountAmount = 0;
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        // Get currency from selected item or fall back to host default
        $currency = strtoupper($selectedItem['currency'] ?? $host->default_currency ?? 'USD');

        return DB::transaction(function () use (
            $host, $client, $type, $purchasableModel, $selectedItem,
            $paymentMethod, $manualMethod, $subtotal, $taxAmount, $discountAmount, $totalAmount, $currency
        ) {
            // Determine initial status
            $status = $paymentMethod === Transaction::METHOD_STRIPE
                ? Transaction::STATUS_PENDING
                : Transaction::STATUS_PENDING;

            $transaction = Transaction::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'type' => $type,
                'purchasable_type' => $purchasableModel ? get_class($purchasableModel) : null,
                'purchasable_id' => $purchasableModel?->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'manual_method' => $manualMethod,
                'metadata' => [
                    'item_name' => $selectedItem['name'] ?? null,
                    'item_datetime' => $selectedItem['datetime'] ?? null,
                    'item_instructor' => $selectedItem['instructor'] ?? null,
                    'item_location' => $selectedItem['location'] ?? null,
                    'is_waitlist' => $selectedItem['is_waitlist'] ?? false,
                    'using_membership' => $selectedItem['using_membership'] ?? false,
                    'membership_id' => $selectedItem['membership_id'] ?? null,
                    'membership_name' => $selectedItem['membership_name'] ?? null,
                    // Series-specific (class_plan with class_booking_type=series).
                    // Captured here so the confirmation email can show the date
                    // range and session count even after the booking session is cleared.
                    'class_booking_type' => $selectedItem['class_booking_type'] ?? null,
                    'billing_period' => $selectedItem['billing_period'] ?? null,
                    'series_summary' => $selectedItem['series_summary'] ?? null,
                    // Direct id of the underlying class/service plan — kept
                    // alongside the polymorphic purchasable link as a
                    // belt-and-braces reference for emails / reports.
                    'class_plan_id' => $selectedItem['class_plan_id'] ?? null,
                    'service_plan_id' => $selectedItem['service_plan_id'] ?? null,
                    // For single class_plan bookings the customer picks a
                    // specific session at contact-info step — record it so we
                    // can create the booking when payment is confirmed.
                    'class_session_id' => $selectedItem['class_session_id'] ?? null,
                    'service_slot_id' => $selectedItem['service_slot_id'] ?? null,
                ],
            ]);

            // Always create an invoice immediately (invoice = "you owe us")
            try {
                $this->createInvoiceFromTransaction($transaction);
            } catch (\Exception $e) {
                Log::warning('Failed to create invoice for transaction', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Create booking(s) after successful payment.
     * Returns the (first) Booking row created, or null if none could be made.
     *
     * Behavior by purchasable type:
     *   ClassSession / ServiceSlot      → one booking against that exact slot
     *   ClassPlan + class_session_id    → one booking against the chosen session
     *   ClassPlan + class_booking_type=series → one booking per published
     *                                            session inside the billing window
     */
    public function createBookingFromTransaction(Transaction $transaction): ?Booking
    {
        $purchasable = $transaction->purchasable;
        if (!$purchasable) {
            return null;
        }

        if ($purchasable instanceof ClassSession || $purchasable instanceof ServiceSlot) {
            return $this->createBookingForBookable($transaction, $purchasable);
        }

        if ($purchasable instanceof \App\Models\ClassPlan) {
            $metadata = $transaction->metadata ?? [];

            if (($metadata['class_booking_type'] ?? 'single') === 'series') {
                return $this->createSeriesBookings($transaction, $purchasable, $metadata);
            }

            $sessionId = $metadata['class_session_id'] ?? null;
            if (!$sessionId) {
                Log::warning('Cannot auto-book single class_plan transaction — no class_session_id in metadata', [
                    'transaction_id' => $transaction->id,
                ]);
                return null;
            }

            $session = ClassSession::where('host_id', $transaction->host_id)->find($sessionId);
            if (!$session) {
                Log::warning('Class session referenced by transaction is missing', [
                    'transaction_id' => $transaction->id,
                    'class_session_id' => $sessionId,
                ]);
                return null;
            }

            return $this->createBookingForBookable($transaction, $session);
        }

        return null;
    }

    /**
     * Create one booking row pointing at a specific bookable (ClassSession or ServiceSlot).
     * Idempotent: if a non-cancelled booking already exists for this
     * (host, client, bookable) tuple, returns it instead of creating a duplicate
     * — so re-confirming a transaction never double-books.
     */
    protected function createBookingForBookable(Transaction $transaction, $bookable, ?float $pricePaid = null, ?string $bookingType = null): Booking
    {
        $existing = Booking::where('host_id', $transaction->host_id)
            ->where('client_id', $transaction->client_id)
            ->where('bookable_type', get_class($bookable))
            ->where('bookable_id', $bookable->id)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->first();
        if ($existing) {
            if (!$transaction->booking_id) {
                $transaction->update(['booking_id' => $existing->id]);
            }

            // If the booking was created earlier while the transaction was
            // still pending, its price_paid is null. Now that the transaction
            // is paid, stamp the price so the booking detail page shows the
            // actual amount received instead of "$0.00".
            if ($existing->price_paid === null && $transaction->status === Transaction::STATUS_PAID) {
                $newPrice = $pricePaid !== null ? $pricePaid : (float) $transaction->total_amount;
                $existing->update(['price_paid' => $newPrice]);
            }

            return $existing;
        }

        $isWaitlist = $transaction->metadata['is_waitlist'] ?? false;
        $paymentMethod = $this->bookingPaymentMethodFor($transaction);
        $type = $bookingType ?? (($transaction->metadata['class_booking_type'] ?? null) === 'series'
            ? Booking::TYPE_SERIES
            : Booking::TYPE_SINGLE);
        // Series bookings get a shared identifier so the index view can fold
        // them into a single row per purchase. Singletons stay null.
        $seriesId = $type === Booking::TYPE_SERIES ? 'TX-' . $transaction->id : null;

        $booking = Booking::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'bookable_type' => get_class($bookable),
            'bookable_id' => $bookable->id,
            'booking_type' => $type,
            'series_id' => $seriesId,
            'status' => $isWaitlist ? Booking::STATUS_WAITLISTED : Booking::STATUS_CONFIRMED,
            'booked_at' => now(),
            'booking_source' => Booking::SOURCE_ONLINE,
            'payment_method' => $paymentMethod,
            'price_paid' => $pricePaid !== null
                ? $pricePaid
                : ($transaction->status === Transaction::STATUS_PAID ? $transaction->total_amount : null),
            'notes' => $isWaitlist ? 'Added to waitlist from public booking' : null,
        ]);

        // Only stamp transaction.booking_id once — it points at the primary booking.
        if (!$transaction->booking_id) {
            $transaction->update(['booking_id' => $booking->id]);
        }

        return $booking;
    }

    /**
     * For a class_plan series transaction, create one Booking per published
     * session of the plan inside the billing window. Returns the first one
     * (or null if no sessions fall in range).
     */
    protected function createSeriesBookings(Transaction $transaction, \App\Models\ClassPlan $plan, array $metadata): ?Booking
    {
        $months = $this->resolveSeriesMonths($metadata);
        if (!$months) {
            Log::warning('Series class_plan transaction has no resolvable billing period', [
                'transaction_id' => $transaction->id,
            ]);
            return null;
        }

        $rangeStart = $transaction->created_at ?? now();
        $rangeEnd = $rangeStart->copy()->addMonths($months);

        $sessions = ClassSession::where('host_id', $transaction->host_id)
            ->where('class_plan_id', $plan->id)
            ->where('status', ClassSession::STATUS_PUBLISHED)
            ->whereBetween('start_time', [$rangeStart, $rangeEnd])
            ->orderBy('start_time')
            ->get();

        if ($sessions->isEmpty()) {
            Log::info('Series class_plan transaction confirmed but no published sessions found in range', [
                'transaction_id' => $transaction->id,
                'class_plan_id' => $plan->id,
                'range_start' => $rangeStart->toDateTimeString(),
                'range_end' => $rangeEnd->toDateTimeString(),
            ]);
            return null;
        }

        // Split the paid amount across the sessions so per-booking price_paid
        // reflects a fair share rather than the full series total on each row.
        $perSessionPrice = $transaction->status === Transaction::STATUS_PAID && $sessions->count() > 0
            ? round(((float) $transaction->total_amount) / $sessions->count(), 2)
            : null;

        $firstBooking = null;
        foreach ($sessions as $session) {
            $booking = $this->createBookingForBookable($transaction, $session, $perSessionPrice, Booking::TYPE_SERIES);
            $firstBooking = $firstBooking ?? $booking;
        }

        return $firstBooking;
    }

    /**
     * Pull the billing period (in months) for a series transaction, looking
     * at the explicit metadata key first, then digging it out of item_name.
     */
    protected function resolveSeriesMonths(array $metadata): ?int
    {
        if (!empty($metadata['billing_period']) && preg_match('/(\d+)/', $metadata['billing_period'], $m)) {
            return (int) $m[1];
        }
        if (is_string($metadata['item_name'] ?? null) && preg_match('/(\d+)\s*Month/i', $metadata['item_name'], $m)) {
            return (int) $m[1];
        }
        return null;
    }

    protected function bookingPaymentMethodFor(Transaction $transaction): string
    {
        return match ($transaction->payment_method) {
            Transaction::METHOD_MANUAL => $transaction->manual_method ?? Booking::PAYMENT_MANUAL,
            Transaction::METHOD_STRIPE => Booking::PAYMENT_STRIPE,
            default => $transaction->payment_method,
        };
    }

    /**
     * Create a booking using membership credits (no payment required)
     */
    public function createMembershipBooking(Transaction $transaction, CustomerMembership $membership): ?Booking
    {
        $purchasable = $transaction->purchasable;

        if (!$purchasable) {
            return null;
        }

        // Only for class sessions
        if (!($purchasable instanceof ClassSession)) {
            return null;
        }

        $isWaitlist = $transaction->metadata['is_waitlist'] ?? false;

        $booking = Booking::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'bookable_type' => get_class($purchasable),
            'bookable_id' => $purchasable->id,
            'status' => $isWaitlist ? Booking::STATUS_WAITLISTED : Booking::STATUS_CONFIRMED,
            'booked_at' => now(),
            'booking_source' => Booking::SOURCE_ONLINE,
            'payment_method' => Booking::PAYMENT_MEMBERSHIP,
            'customer_membership_id' => $membership->id,
            'credits_used' => 1,
            'price_paid' => 0, // Free with membership
            'notes' => 'Booked using membership: ' . ($membership->membershipPlan?->name ?? 'Membership'),
        ]);

        // Link booking to transaction
        $transaction->update(['booking_id' => $booking->id]);

        return $booking;
    }

    /**
     * Activate a membership purchase
     */
    public function activateMembershipPurchase(Transaction $transaction): ?CustomerMembership
    {
        if ($transaction->type !== Transaction::TYPE_MEMBERSHIP_PURCHASE) {
            return null;
        }

        $plan = $transaction->purchasable;
        if (!($plan instanceof MembershipPlan)) {
            return null;
        }

        $startDate = now();

        // Honor a prepaid multi-month window if the customer chose one at
        // checkout (stored as e.g. "3 months" in the transaction metadata);
        // otherwise fall back to the plan's natural interval. Only the explicit
        // billing_period is trusted here — not item_name — so a number in the
        // plan name can't be mistaken for a prepay window.
        $billingPeriod = $transaction->metadata['billing_period'] ?? null;
        $prepaidMonths = (is_string($billingPeriod) && preg_match('/(\d+)\s*month/i', $billingPeriod, $m))
            ? (int) $m[1]
            : null;
        $endDate = ($prepaidMonths && $prepaidMonths > 1)
            ? $startDate->copy()->addMonths($prepaidMonths)
            : $this->calculateMembershipEndDate($plan, $startDate);

        // Determine credits based on plan type
        $creditsRemaining = null; // null = unlimited
        $creditsPerPeriod = null;
        if ($plan->type === MembershipPlan::TYPE_CREDITS) {
            $creditsRemaining = $plan->credits_per_cycle;
            $creditsPerPeriod = $plan->credits_per_cycle;
        }

        $membership = CustomerMembership::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'membership_plan_id' => $plan->id,
            'status' => CustomerMembership::STATUS_ACTIVE,
            'payment_method' => $transaction->payment_method,
            'credits_remaining' => $creditsRemaining,
            'credits_per_period' => $creditsPerPeriod,
            'current_period_start' => $startDate,
            'current_period_end' => $endDate,
            'started_at' => $startDate,
            'expires_at' => $endDate,
        ]);

        // Update client status
        $transaction->client->convertToMember();

        return $membership;
    }

    /**
     * Activate a class pass (pack) purchase.
     *
     * Class packs and class passes share the `class_passes` table; the live
     * purchase record is a ClassPassPurchase (the legacy ClassPackPurchase model
     * points at a dropped table). Mirrors ClassPassService::create so the credit
     * pack shows up in the member portal, reports, and booking credit lookups.
     */
    public function activateClassPackPurchase(Transaction $transaction): ?ClassPassPurchase
    {
        if ($transaction->type !== Transaction::TYPE_CLASS_PACK_PURCHASE) {
            return null;
        }

        // The purchasable is stored as a ClassPack instance, but both models map
        // to class_passes — reload as ClassPass for the richer pass API.
        $pass = ClassPass::where('host_id', $transaction->host_id)
            ->find($transaction->purchasable_id);
        if (!$pass) {
            return null;
        }

        $now = now();
        $activationType = $pass->activation_type ?? ClassPass::ACTIVATION_ON_PURCHASE;

        // On-purchase passes start (and begin counting down their validity) now;
        // on-first-booking passes activate later when the first credit is used.
        $activatedAt = null;
        $expiresAt = null;
        if ($activationType === ClassPass::ACTIVATION_ON_PURCHASE) {
            $activatedAt = $now;
            $expiresAt = $pass->calculateExpirationDate($now);
        }

        return ClassPassPurchase::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'class_pass_id' => $pass->id,
            'classes_remaining' => $pass->class_count,
            'classes_total' => $pass->class_count,
            'credits_used' => 0,
            'purchased_at' => $now,
            'activated_at' => $activatedAt,
            'activation_type' => $activationType,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Process successful payment - create bookings/memberships/packs
     */
    public function processSuccessfulPayment(Transaction $transaction): array
    {
        $results = [
            'booking' => null,
            'membership' => null,
            'class_pack' => null,
            'invoice' => null,
        ];

        DB::transaction(function () use ($transaction, &$results) {
            // Mark as paid
            $transaction->markPaid();

            // Create appropriate records based on type
            switch ($transaction->type) {
                case Transaction::TYPE_CLASS_BOOKING:
                case Transaction::TYPE_SERVICE_BOOKING:
                    $results['booking'] = $this->createBookingFromTransaction($transaction);
                    break;

                case Transaction::TYPE_MEMBERSHIP_PURCHASE:
                    $results['membership'] = $this->activateMembershipPurchase($transaction);
                    break;

                case Transaction::TYPE_CLASS_PACK_PURCHASE:
                    $results['class_pack'] = $this->activateClassPackPurchase($transaction);
                    break;
            }

            // Mark existing invoice as paid, or create one if missing
            $transaction->load('invoice');
            if ($transaction->invoice) {
                $transaction->invoice->markPaid();
                $results['invoice'] = $transaction->invoice;
            } else {
                $results['invoice'] = $this->createInvoiceFromTransaction($transaction);
            }
        });

        // Send confirmation email (outside of DB transaction)
        $this->sendConfirmationEmail($transaction, $results['booking']);

        // Assign intake forms if applicable
        $results['intake_forms'] = $this->assignIntakeForms($transaction, $results['booking']);

        return $results;
    }

    /**
     * Create an invoice from a transaction
     */
    protected function createInvoiceFromTransaction(Transaction $transaction): Invoice
    {
        $invoiceService = app(InvoiceService::class);
        return $invoiceService->createFromTransaction($transaction);
    }

    /**
     * Send confirmation email with calendar invite and invoice
     */
    public function sendConfirmationEmail(Transaction $transaction, ?Booking $booking = null): void
    {
        try {
            $transaction->load(['client', 'host', 'invoice', 'purchasable']);

            if (!$transaction->client?->email) {
                Log::warning('Cannot send confirmation email: no client email', [
                    'transaction_id' => $transaction->transaction_id,
                ]);
                return;
            }

            // Generate calendar invite for class/service bookings
            $icsContent = null;
            if (in_array($transaction->type, [Transaction::TYPE_CLASS_BOOKING, Transaction::TYPE_SERVICE_BOOKING])) {
                $calendarService = app(CalendarInviteService::class);
                $icsContent = $calendarService->generateFromTransaction($transaction);
            }

            // Generate invoice PDF
            $pdfContent = null;
            if ($transaction->invoice) {
                $invoiceService = app(InvoiceService::class);
                $pdfContent = $invoiceService->getPdfContent($transaction->invoice);
            }

            // Generate schedule selection link for membership purchases
            $scheduleSelectionUrl = null;
            if ($transaction->type === Transaction::TYPE_MEMBERSHIP_PURCHASE) {
                $membership = \App\Models\CustomerMembership::where('client_id', $transaction->client_id)
                    ->where('membership_plan_id', $transaction->purchasable_id)
                    ->whereNotNull('access_token')
                    ->latest()
                    ->first();

                if ($membership && $transaction->host->subdomain) {
                    $scheduleSelectionUrl = route('subdomain.membership-access', [
                        'subdomain' => $transaction->host->subdomain,
                        'accessToken' => $membership->access_token,
                    ]);
                }
            }

            $mail = new TransactionConfirmationMail(
                $transaction,
                $booking,
                $icsContent,
                $pdfContent,
                $scheduleSelectionUrl
            );

            // Send immediately (bypass queue) so email appears in logs and is delivered right away
            Mail::to($transaction->client->email)->sendNow($mail);

            // Mark invoice as sent
            if ($transaction->invoice && $transaction->invoice->status === Invoice::STATUS_DRAFT) {
                $transaction->invoice->markSent();
            }

            Log::info('Confirmation email sent', [
                'transaction_id' => $transaction->transaction_id,
                'email' => $transaction->client->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Assign intake forms to a booking based on the plan's questionnaire attachments
     */
    public function assignIntakeForms(Transaction $transaction, ?Booking $booking = null): array
    {
        $responses = [];

        try {
            $purchasable = $transaction->purchasable;
            if (!$purchasable) {
                return $responses;
            }

            // Get the plan from the purchasable
            $plan = $this->getPlanFromPurchasable($purchasable);
            if (!$plan) {
                return $responses;
            }

            // Get questionnaire attachments for this plan with after_booking timing
            $attachments = QuestionnaireAttachment::where('attachable_type', get_class($plan))
                ->where('attachable_id', $plan->id)
                ->where('collection_timing', QuestionnaireAttachment::TIMING_AFTER_BOOKING)
                ->with('questionnaire.publishedVersion')
                ->get();

            if ($attachments->isEmpty()) {
                return $responses;
            }

            $client = $transaction->client;
            $host = $transaction->host;

            foreach ($attachments as $attachment) {
                // Check if this is first-time only and client has already completed it
                if ($attachment->isFirstTimeOnly()) {
                    $existingComplete = QuestionnaireResponse::where('client_id', $client->id)
                        ->where('host_id', $host->id)
                        ->whereHas('version', function ($q) use ($attachment) {
                            $q->where('questionnaire_id', $attachment->questionnaire_id);
                        })
                        ->completed()
                        ->exists();

                    if ($existingComplete) {
                        continue; // Skip, already completed
                    }
                }

                $questionnaire = $attachment->questionnaire;
                if (!$questionnaire || !$questionnaire->publishedVersion) {
                    continue;
                }

                // Create questionnaire response
                $response = QuestionnaireResponse::create([
                    'questionnaire_version_id' => $questionnaire->publishedVersion->id,
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'booking_id' => $booking?->id,
                    'status' => QuestionnaireResponse::STATUS_PENDING,
                    'current_step' => 1,
                ]);

                $responses[] = $response;

                Log::info('Intake form assigned', [
                    'transaction_id' => $transaction->transaction_id,
                    'questionnaire_id' => $questionnaire->id,
                    'response_id' => $response->id,
                ]);
            }

            // Update booking intake status if we created responses
            if ($booking && count($responses) > 0) {
                $booking->update(['intake_status' => Booking::INTAKE_PENDING]);
            }

            // Send intake form request email if we have responses
            if (count($responses) > 0) {
                $this->sendIntakeFormEmail($transaction, $responses);
            }

        } catch (\Exception $e) {
            Log::error('Failed to assign intake forms', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $responses;
    }

    /**
     * Get the plan from a purchasable (ClassSession -> ClassPlan, etc.)
     */
    protected function getPlanFromPurchasable($purchasable)
    {
        if ($purchasable instanceof ClassSession) {
            return $purchasable->classPlan;
        }
        if ($purchasable instanceof ServiceSlot) {
            return $purchasable->servicePlan;
        }
        if ($purchasable instanceof MembershipPlan) {
            return $purchasable;
        }
        if ($purchasable instanceof ClassPack) {
            return $purchasable;
        }
        return null;
    }

    /**
     * Send intake form request email
     */
    protected function sendIntakeFormEmail(Transaction $transaction, array $responses): void
    {
        try {
            if (!$transaction->client?->email || empty($responses)) {
                return;
            }

            // Check if IntakeFormRequestMail exists
            if (!class_exists(IntakeFormRequestMail::class)) {
                Log::info('IntakeFormRequestMail not found, skipping email');
                return;
            }

            Mail::to($transaction->client->email)
                ->send(new IntakeFormRequestMail($transaction, $responses));

            Log::info('Intake form request email sent', [
                'transaction_id' => $transaction->transaction_id,
                'response_count' => count($responses),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send intake form email', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine transaction type from booking type
     */
    protected function determineTransactionType(string $bookingType): string
    {
        return match ($bookingType) {
            'class_session' => Transaction::TYPE_CLASS_BOOKING,
            'service_slot' => Transaction::TYPE_SERVICE_BOOKING,
            'membership_plan' => Transaction::TYPE_MEMBERSHIP_PURCHASE,
            'class_pack' => Transaction::TYPE_CLASS_PACK_PURCHASE,
            'event' => Transaction::TYPE_EVENT_REGISTRATION,
            default => Transaction::TYPE_CLASS_BOOKING,
        };
    }

    /**
     * Get the purchasable model from selected item
     */
    protected function getPurchasableModel(array $selectedItem): ?object
    {
        $type = $selectedItem['type'] ?? null;
        $id = $selectedItem['id'] ?? null;

        if (!$type || !$id) {
            return null;
        }

        return match ($type) {
            'class_session' => ClassSession::find($id),
            'service_slot' => ServiceSlot::find($id),
            'membership_plan' => MembershipPlan::find($id),
            'class_pack' => ClassPack::find($id),
            // class_plan bookings (both single + series) — the purchasable
            // points at the class plan so confirmation emails / reports can
            // navigate back to it.
            'class_plan' => \App\Models\ClassPlan::find($selectedItem['class_plan_id'] ?? $id),
            'service_plan' => \App\Models\ServicePlan::find($selectedItem['service_plan_id'] ?? $id),
            // Event registrations point the purchasable at the event so the
            // confirmation page / reports can navigate back to it.
            'event' => Event::find($id),
            default => null,
        };
    }

    /**
     * Calculate membership end date based on interval
     */
    protected function calculateMembershipEndDate(MembershipPlan $plan, $startDate): \Carbon\Carbon
    {
        $start = \Carbon\Carbon::parse($startDate);

        return match ($plan->interval) {
            MembershipPlan::INTERVAL_MONTHLY => $start->addMonth(),
            MembershipPlan::INTERVAL_YEARLY => $start->addYear(),
            default => $start->addMonth(),
        };
    }

    /**
     * Get manual payment instructions for a method
     */
    public function getManualPaymentInstructions(Host $host, string $method): ?string
    {
        $paymentSettings = $host->payment_settings ?? [];
        $manualMethods = $paymentSettings['manual_methods'] ?? [];

        // Check new structure first
        if (isset($manualMethods[$method]['instructions'])) {
            return $manualMethods[$method]['instructions'];
        }

        // Fallback to old structure for backwards compatibility
        return $paymentSettings[$method . '_instructions'] ?? null;
    }
}
