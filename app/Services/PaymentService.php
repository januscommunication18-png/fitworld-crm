<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClassPassPurchase;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Process a manual payment (cash, check, venmo, etc.)
     */
    public function processManualPayment(
        Host $host,
        Client $client,
        float $amount,
        string $manualMethod,
        ?Booking $booking = null,
        ?Model $payable = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($host, $client, $amount, $manualMethod, $booking, $payable, $notes) {
            $payment = Payment::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking?->id,
                'payable_type' => $payable ? get_class($payable) : null,
                'payable_id' => $payable?->id,
                'amount' => $amount,
                'payment_method' => Payment::METHOD_MANUAL,
                'manual_method' => $manualMethod,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $notes,
                'processed_by_user_id' => auth()->id(),
            ]);

            $this->auditService->logPaymentProcessed($payment);

            // Create transaction record for cash flow tracking
            $this->createTransactionFromPayment($host, $client, $booking, $amount, Transaction::METHOD_MANUAL, $manualMethod, $notes);

            return $payment;
        });
    }

    /**
     * Process a membership credit payment (no money exchanged)
     */
    public function processMembershipPayment(
        Host $host,
        Client $client,
        CustomerMembership $membership,
        ?Booking $booking = null
    ): Payment {
        return DB::transaction(function () use ($host, $client, $membership, $booking) {
            $payment = Payment::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking?->id,
                'payable_type' => CustomerMembership::class,
                'payable_id' => $membership->id,
                'amount' => 0,
                'payment_method' => Payment::METHOD_MEMBERSHIP,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => 'Paid via membership credits',
                'processed_by_user_id' => auth()->id(),
            ]);

            $this->auditService->logPaymentProcessed($payment);

            // Create transaction record for cash flow tracking
            $this->createTransactionFromPayment($host, $client, $booking, 0, Transaction::METHOD_MEMBERSHIP, null, 'Paid via membership: ' . $membership->membershipPlan?->name);

            return $payment;
        });
    }

    /**
     * Process a class pass credit payment (no money exchanged)
     */
    public function processPassPayment(
        Host $host,
        Client $client,
        ClassPassPurchase $passPurchase,
        ?Booking $booking = null
    ): Payment {
        return DB::transaction(function () use ($host, $client, $passPurchase, $booking) {
            $payment = Payment::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking?->id,
                'payable_type' => ClassPassPurchase::class,
                'payable_id' => $passPurchase->id,
                'amount' => 0,
                'payment_method' => Payment::METHOD_PACK,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => 'Paid via class pass credits',
                'processed_by_user_id' => auth()->id(),
            ]);

            $this->auditService->logPaymentProcessed($payment);

            // Create transaction record for cash flow tracking
            $this->createTransactionFromPayment($host, $client, $booking, 0, Transaction::METHOD_PACK, null, 'Paid via class pass: ' . $passPurchase->classPass?->name);

            return $payment;
        });
    }

    /**
     * @deprecated Use processPassPayment instead
     */
    public function processPackPayment(
        Host $host,
        Client $client,
        ClassPassPurchase $passPurchase,
        ?Booking $booking = null
    ): Payment {
        return $this->processPassPayment($host, $client, $passPurchase, $booking);
    }

    /**
     * Process a complimentary booking (no charge)
     */
    public function processCompPayment(
        Host $host,
        Client $client,
        ?Booking $booking = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($host, $client, $booking, $notes) {
            $payment = Payment::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking?->id,
                'amount' => 0,
                'payment_method' => Payment::METHOD_COMP,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $notes ?? 'Complimentary',
                'processed_by_user_id' => auth()->id(),
            ]);

            $this->auditService->logPaymentProcessed($payment);

            // Create transaction record for cash flow tracking
            $this->createTransactionFromPayment($host, $client, $booking, 0, Transaction::METHOD_COMP, null, $notes ?? 'Complimentary');

            return $payment;
        });
    }

    /**
     * Process a refund
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $amount, $reason) {
            $payment->refund($amount, $reason);

            $this->auditService->logPaymentRefunded($payment, $reason);

            return $payment->fresh();
        });
    }

    /**
     * Get available payment methods for a client
     */
    public function getAvailablePaymentMethods(
        Host $host,
        Client $client,
        ?int $classPlanId = null
    ): array {
        $methods = [];

        // Check for active membership
        $activeMembership = $client->customerMemberships()
            ->active()
            ->notExpired()
            ->withCredits()
            ->with('membershipPlan')
            ->first();

        if ($activeMembership) {
            $methods['membership'] = [
                'available' => true,
                'membership' => $activeMembership,
                'credits_remaining' => $activeMembership->is_unlimited
                    ? 'Unlimited'
                    : $activeMembership->credits_remaining,
                'label' => $activeMembership->membershipPlan->name,
            ];
        }

        // Check for usable class passes (including pending activation)
        $usablePasses = $client->classPassPurchases()
            ->with('classPass')
            ->where('classes_remaining', '>', 0)
            ->where('is_frozen', false)
            ->whereNull('transferred_to_client_id')
            ->where(function ($query) {
                // Either activated and not expired
                $query->where(function ($q) {
                    $q->whereNotNull('activated_at')
                      ->where(function ($q2) {
                          $q2->whereNull('expires_at')
                             ->orWhere('expires_at', '>', now());
                      });
                })
                // Or pending activation (on_first_booking)
                ->orWhere(function ($q) {
                    $q->whereNull('activated_at')
                      ->where('activation_type', \App\Models\ClassPassPurchase::ACTIVATION_ON_FIRST_BOOKING);
                });
            })
            ->orderBy('expires_at', 'asc')
            ->get();

        // Filter passes by class plan eligibility if class_plan_id provided
        if ($classPlanId && $usablePasses->isNotEmpty()) {
            $classPlan = \App\Models\ClassPlan::find($classPlanId);
            if ($classPlan) {
                $usablePasses = $usablePasses->filter(function ($purchase) use ($classPlan) {
                    return $purchase->classPass->coversClassPlan($classPlan);
                });
            }
        }

        if ($usablePasses->isNotEmpty()) {
            $methods['pack'] = [
                'available' => true,
                'packs' => $usablePasses->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'name' => $purchase->classPass->name,
                        'classes_remaining' => $purchase->classes_remaining,
                        'expires_at' => $purchase->expires_at?->format('M j, Y'),
                        'is_pending_activation' => $purchase->is_pending_activation,
                    ];
                })->toArray(),
            ];
        }

        // Manual payment is always available
        $methods['manual'] = [
            'available' => true,
            'methods' => Payment::getManualMethods(),
        ];

        // Stripe is available if configured (check host settings)
        $stripeEnabled = $host->stripe_account_id !== null;
        if ($stripeEnabled) {
            $methods['stripe'] = [
                'available' => true,
                'label' => 'Card Payment',
            ];
        }

        // Comp is available for admin/owner only
        $user = auth()->user();
        if ($user && in_array($user->role, ['owner', 'admin'])) {
            $methods['comp'] = [
                'available' => true,
                'label' => 'Complimentary',
            ];
        }

        return $methods;
    }

    /**
     * Create a pending payment for Stripe
     */
    public function createPendingStripePayment(
        Host $host,
        Client $client,
        float $amount,
        ?Booking $booking = null,
        ?Model $payable = null
    ): Payment {
        return Payment::create([
            'host_id' => $host->id,
            'client_id' => $client->id,
            'booking_id' => $booking?->id,
            'payable_type' => $payable ? get_class($payable) : null,
            'payable_id' => $payable?->id,
            'amount' => $amount,
            'payment_method' => Payment::METHOD_STRIPE,
            'status' => Payment::STATUS_PENDING,
            'processed_by_user_id' => auth()->id(),
        ]);
    }

    /**
     * Complete a Stripe payment
     */
    public function completeStripePayment(
        Payment $payment,
        string $paymentIntentId,
        ?string $chargeId = null
    ): Payment {
        return DB::transaction(function () use ($payment, $paymentIntentId, $chargeId) {
            $payment->update([
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_charge_id' => $chargeId,
                'status' => Payment::STATUS_COMPLETED,
            ]);

            $this->auditService->logPaymentProcessed($payment);

            return $payment->fresh();
        });
    }

    /**
     * Mark payment as failed
     */
    public function markPaymentFailed(Payment $payment, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment->markFailed();

            $this->auditService->logPaymentFailed($payment, $reason);

            return $payment->fresh();
        });
    }

    /**
     * Create a Transaction record from a walk-in payment for cash flow tracking
     */
    protected function createTransactionFromPayment(
        Host $host,
        Client $client,
        ?Booking $booking,
        float $amount,
        string $paymentMethod,
        ?string $manualMethod = null,
        ?string $notes = null
    ): Transaction {
        // Determine transaction type from booking
        $type = Transaction::TYPE_CLASS_BOOKING;
        $purchasableType = null;
        $purchasableId = null;
        $itemName = null;

        if ($booking && $booking->bookable) {
            $bookable = $booking->bookable;
            if ($bookable instanceof \App\Models\ClassSession) {
                $type = Transaction::TYPE_CLASS_BOOKING;
                $purchasableType = get_class($bookable);
                $purchasableId = $bookable->id;
                $itemName = $bookable->display_title ?? $bookable->classPlan?->name ?? 'Class Session';
            } elseif ($bookable instanceof \App\Models\ServiceSlot) {
                $type = Transaction::TYPE_SERVICE_BOOKING;
                $purchasableType = get_class($bookable);
                $purchasableId = $bookable->id;
                $itemName = $bookable->title ?? $bookable->servicePlan?->name ?? 'Service Slot';
            }
        }

        return Transaction::create([
            'host_id' => $host->id,
            'client_id' => $client->id,
            'booking_id' => $booking?->id,
            'type' => $type,
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasableId,
            'subtotal' => $amount,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $amount,
            'currency' => $host->default_currency ?? 'USD',
            'status' => Transaction::STATUS_PAID,
            'payment_method' => $paymentMethod,
            'manual_method' => $manualMethod,
            'paid_at' => now(),
            'metadata' => [
                'item_name' => $itemName,
                'source' => 'walk_in',
            ],
            'notes' => $notes,
        ]);
    }
}
