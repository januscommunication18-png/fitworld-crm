<?php

namespace App\Services;

use App\Mail\IntakeFormRequestMail;
use App\Mail\TransactionConfirmationMail;
use App\Models\Booking;
use App\Models\ClassPack;
use App\Models\ClassPackPurchase;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
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

        return DB::transaction(function () use (
            $host, $client, $type, $purchasableModel, $selectedItem,
            $paymentMethod, $manualMethod, $subtotal, $taxAmount, $discountAmount, $totalAmount
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
                'currency' => 'USD',
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
                ],
            ]);

            return $transaction;
        });
    }

    /**
     * Create a booking after successful payment
     */
    public function createBookingFromTransaction(Transaction $transaction): ?Booking
    {
        $purchasable = $transaction->purchasable;

        if (!$purchasable) {
            return null;
        }

        // Only create bookings for class sessions and service slots
        if (!($purchasable instanceof ClassSession) && !($purchasable instanceof ServiceSlot)) {
            return null;
        }

        $isWaitlist = $transaction->metadata['is_waitlist'] ?? false;

        // Map transaction payment method to booking payment method
        $paymentMethod = $transaction->payment_method;
        if ($paymentMethod === Transaction::METHOD_MANUAL) {
            // For manual payments, use the specific manual method or default to 'manual'
            $paymentMethod = $transaction->manual_method ?? Booking::PAYMENT_MANUAL;
        } elseif ($paymentMethod === Transaction::METHOD_STRIPE) {
            $paymentMethod = Booking::PAYMENT_STRIPE;
        }

        $booking = Booking::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'bookable_type' => get_class($purchasable),
            'bookable_id' => $purchasable->id,
            'status' => $isWaitlist ? Booking::STATUS_WAITLISTED : Booking::STATUS_CONFIRMED,
            'booked_at' => now(),
            'source' => 'website',
            'payment_method' => $paymentMethod,
            'price_paid' => $transaction->status === Transaction::STATUS_PAID ? $transaction->total_amount : null,
            'notes' => $isWaitlist ? 'Added to waitlist from public booking' : null,
        ]);

        // Link booking to transaction
        $transaction->update(['booking_id' => $booking->id]);

        return $booking;
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
            'booking_source' => 'online',
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
        $endDate = $this->calculateMembershipEndDate($plan, $startDate);

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
     * Activate a class pack purchase
     */
    public function activateClassPackPurchase(Transaction $transaction): ?ClassPackPurchase
    {
        if ($transaction->type !== Transaction::TYPE_CLASS_PACK_PURCHASE) {
            return null;
        }

        $pack = $transaction->purchasable;
        if (!($pack instanceof ClassPack)) {
            return null;
        }

        $expiresAt = $pack->validity_days
            ? now()->addDays($pack->validity_days)
            : null;

        $purchase = ClassPackPurchase::create([
            'host_id' => $transaction->host_id,
            'client_id' => $transaction->client_id,
            'class_pack_id' => $pack->id,
            'classes_remaining' => $pack->class_count,
            'classes_used' => 0,
            'price_paid' => $transaction->total_amount,
            'purchased_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        return $purchase;
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

            // Create invoice
            $results['invoice'] = $this->createInvoiceFromTransaction($transaction);
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

            Mail::to($transaction->client->email)
                ->send(new TransactionConfirmationMail(
                    $transaction,
                    $booking,
                    $icsContent,
                    $pdfContent
                ));

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
