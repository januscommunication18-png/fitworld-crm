<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClassPackPurchase;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\Payment;
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

            return $payment;
        });
    }

    /**
     * Process a class pack credit payment (no money exchanged)
     */
    public function processPackPayment(
        Host $host,
        Client $client,
        ClassPackPurchase $packPurchase,
        ?Booking $booking = null
    ): Payment {
        return DB::transaction(function () use ($host, $client, $packPurchase, $booking) {
            $payment = Payment::create([
                'host_id' => $host->id,
                'client_id' => $client->id,
                'booking_id' => $booking?->id,
                'payable_type' => ClassPackPurchase::class,
                'payable_id' => $packPurchase->id,
                'amount' => 0,
                'payment_method' => Payment::METHOD_PACK,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => 'Paid via class pack credits',
                'processed_by_user_id' => auth()->id(),
            ]);

            $this->auditService->logPaymentProcessed($payment);

            return $payment;
        });
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

        // Check for usable class packs
        $usablePacks = $client->classPackPurchases()
            ->usable()
            ->with('classPack')
            ->get();

        if ($usablePacks->isNotEmpty()) {
            $methods['pack'] = [
                'available' => true,
                'packs' => $usablePacks->map(function ($pack) {
                    return [
                        'id' => $pack->id,
                        'name' => $pack->classPack->name,
                        'classes_remaining' => $pack->classes_remaining,
                        'expires_at' => $pack->expires_at?->format('M j, Y'),
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
}
