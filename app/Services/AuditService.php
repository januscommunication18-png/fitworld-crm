<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\CustomerMembership;
use App\Models\ClassPackPurchase;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Log an action
     */
    public function log(
        int $hostId,
        string $action,
        ?Model $auditable = null,
        array $options = []
    ): AuditLog {
        return AuditLog::log($hostId, $action, $auditable, $options);
    }

    /**
     * Log a booking creation
     */
    public function logBookingCreated(Booking $booking, array $context = []): AuditLog
    {
        return $this->log(
            $booking->host_id,
            AuditLog::ACTION_BOOKING_CREATED,
            $booking,
            [
                'context' => array_merge([
                    'client_id' => $booking->client_id,
                    'bookable_type' => $booking->bookable_type,
                    'bookable_id' => $booking->bookable_id,
                    'booking_source' => $booking->booking_source,
                    'payment_method' => $booking->payment_method,
                ], $context),
            ]
        );
    }

    /**
     * Log a booking cancellation
     */
    public function logBookingCancelled(Booking $booking, ?string $reason = null): AuditLog
    {
        return $this->log(
            $booking->host_id,
            AuditLog::ACTION_BOOKING_CANCELLED,
            $booking,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $booking->client_id,
                    'cancelled_at' => $booking->cancelled_at?->toISOString(),
                ],
            ]
        );
    }

    /**
     * Log a client check-in
     */
    public function logBookingCheckedIn(Booking $booking): AuditLog
    {
        return $this->log(
            $booking->host_id,
            AuditLog::ACTION_BOOKING_CHECKED_IN,
            $booking,
            [
                'context' => [
                    'client_id' => $booking->client_id,
                    'checked_in_at' => $booking->checked_in_at?->toISOString(),
                ],
            ]
        );
    }

    /**
     * Log a capacity override
     */
    public function logCapacityOverride(Booking $booking, string $reason): AuditLog
    {
        return AuditLog::logCapacityOverride($booking, $reason);
    }

    /**
     * Log an intake waiver
     */
    public function logIntakeWaive(Booking $booking, string $reason): AuditLog
    {
        return AuditLog::logIntakeWaive($booking, $reason);
    }

    /**
     * Log a payment processed
     */
    public function logPaymentProcessed(Payment $payment): AuditLog
    {
        return AuditLog::logPaymentAction($payment, AuditLog::ACTION_PAYMENT_PROCESSED);
    }

    /**
     * Log a payment refund
     */
    public function logPaymentRefunded(Payment $payment, ?string $reason = null): AuditLog
    {
        return $this->log(
            $payment->host_id,
            AuditLog::ACTION_PAYMENT_REFUNDED,
            $payment,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $payment->client_id,
                    'amount' => $payment->amount,
                    'refunded_amount' => $payment->refunded_amount,
                    'method' => $payment->payment_method,
                ],
            ]
        );
    }

    /**
     * Log a payment failure
     */
    public function logPaymentFailed(Payment $payment, ?string $reason = null): AuditLog
    {
        return $this->log(
            $payment->host_id,
            AuditLog::ACTION_PAYMENT_FAILED,
            $payment,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $payment->client_id,
                    'amount' => $payment->amount,
                    'method' => $payment->payment_method,
                ],
            ]
        );
    }

    /**
     * Log membership creation
     */
    public function logMembershipCreated(CustomerMembership $membership): AuditLog
    {
        return $this->log(
            $membership->host_id,
            AuditLog::ACTION_MEMBERSHIP_CREATED,
            $membership,
            [
                'context' => [
                    'client_id' => $membership->client_id,
                    'membership_plan_id' => $membership->membership_plan_id,
                    'payment_method' => $membership->payment_method,
                ],
            ]
        );
    }

    /**
     * Log membership pause
     */
    public function logMembershipPaused(CustomerMembership $membership, ?string $reason = null): AuditLog
    {
        return $this->log(
            $membership->host_id,
            AuditLog::ACTION_MEMBERSHIP_PAUSED,
            $membership,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $membership->client_id,
                    'paused_at' => $membership->paused_at?->toISOString(),
                ],
            ]
        );
    }

    /**
     * Log membership resume
     */
    public function logMembershipResumed(CustomerMembership $membership): AuditLog
    {
        return $this->log(
            $membership->host_id,
            AuditLog::ACTION_MEMBERSHIP_RESUMED,
            $membership,
            [
                'context' => [
                    'client_id' => $membership->client_id,
                ],
            ]
        );
    }

    /**
     * Log membership cancellation
     */
    public function logMembershipCancelled(CustomerMembership $membership, ?string $reason = null): AuditLog
    {
        return $this->log(
            $membership->host_id,
            AuditLog::ACTION_MEMBERSHIP_CANCELLED,
            $membership,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $membership->client_id,
                    'cancelled_at' => $membership->cancelled_at?->toISOString(),
                ],
            ]
        );
    }

    /**
     * Log pack purchase
     */
    public function logPackPurchased(ClassPackPurchase $purchase): AuditLog
    {
        return $this->log(
            $purchase->host_id,
            AuditLog::ACTION_PACK_PURCHASED,
            $purchase,
            [
                'context' => [
                    'client_id' => $purchase->client_id,
                    'class_pack_id' => $purchase->class_pack_id,
                    'classes_total' => $purchase->classes_total,
                ],
            ]
        );
    }

    /**
     * Log pack credit used
     */
    public function logPackCreditUsed(ClassPackPurchase $purchase, Booking $booking): AuditLog
    {
        return $this->log(
            $purchase->host_id,
            AuditLog::ACTION_PACK_CREDIT_USED,
            $purchase,
            [
                'context' => [
                    'client_id' => $purchase->client_id,
                    'booking_id' => $booking->id,
                    'classes_remaining' => $purchase->classes_remaining,
                ],
            ]
        );
    }

    /**
     * Log pack credit restored
     */
    public function logPackCreditRestored(ClassPackPurchase $purchase, ?Booking $booking = null): AuditLog
    {
        return $this->log(
            $purchase->host_id,
            AuditLog::ACTION_PACK_CREDIT_RESTORED,
            $purchase,
            [
                'context' => [
                    'client_id' => $purchase->client_id,
                    'booking_id' => $booking?->id,
                    'classes_remaining' => $purchase->classes_remaining,
                ],
            ]
        );
    }
}
