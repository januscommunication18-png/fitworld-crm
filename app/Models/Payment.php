<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class Payment extends Model
{
    use HasFactory;

    // Payment method constants
    const METHOD_STRIPE = 'stripe';
    const METHOD_MEMBERSHIP = 'membership';
    const METHOD_PACK = 'pack';
    const METHOD_MANUAL = 'manual';
    const METHOD_COMP = 'comp';

    // Manual payment method types
    const MANUAL_CASH = 'cash';
    const MANUAL_CHECK = 'check';
    const MANUAL_VENMO = 'venmo';
    const MANUAL_ZELLE = 'zelle';
    const MANUAL_PAYPAL = 'paypal';
    const MANUAL_CASH_APP = 'cash_app';
    const MANUAL_BANK_TRANSFER = 'bank_transfer';
    const MANUAL_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $fillable = [
        'host_id',
        'client_id',
        'booking_id',
        'payable_type',
        'payable_id',
        'amount',
        'currency',
        'payment_method',
        'manual_method',
        'status',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'refunded_amount',
        'refund_reason',
        'refunded_at',
        'notes',
        'processed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedRefundedAmountAttribute(): string
    {
        if (!$this->refunded_amount) {
            return '$0.00';
        }
        return '$' . number_format($this->refunded_amount, 2);
    }

    public function getNetAmountAttribute(): float
    {
        return $this->amount - ($this->refunded_amount ?? 0);
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return '$' . number_format($this->net_amount, 2);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsRefundedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    public function getIsStripeAttribute(): bool
    {
        return $this->payment_method === self::METHOD_STRIPE;
    }

    public function getIsManualAttribute(): bool
    {
        return $this->payment_method === self::METHOD_MANUAL;
    }

    public function getIsCompAttribute(): bool
    {
        return $this->payment_method === self::METHOD_COMP;
    }

    public function getIsCreditBasedAttribute(): bool
    {
        return in_array($this->payment_method, [self::METHOD_MEMBERSHIP, self::METHOD_PACK]);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method === self::METHOD_MANUAL && $this->manual_method) {
            return self::getManualMethods()[$this->manual_method] ?? ucfirst($this->manual_method);
        }
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_FAILED => 'badge-error',
            self::STATUS_REFUNDED => 'badge-neutral',
            self::STATUS_PARTIALLY_REFUNDED => 'badge-info',
            default => 'badge-neutral',
        };
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            self::METHOD_STRIPE => 'badge-primary',
            self::METHOD_MEMBERSHIP => 'badge-secondary',
            self::METHOD_PACK => 'badge-accent',
            self::METHOD_MANUAL => 'badge-info',
            self::METHOD_COMP => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark as failed
     */
    public function markFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Process a refund
     */
    public function refund(float $amount, ?string $reason = null): void
    {
        $newRefundedAmount = ($this->refunded_amount ?? 0) + $amount;
        $isFullRefund = $newRefundedAmount >= $this->amount;

        $this->update([
            'refunded_amount' => $newRefundedAmount,
            'refund_reason' => $reason ?? $this->refund_reason,
            'refunded_at' => now(),
            'status' => $isFullRefund ? self::STATUS_REFUNDED : self::STATUS_PARTIALLY_REFUNDED,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('payment_method', self::METHOD_MANUAL);
    }

    public function scopeStripe(Builder $query): Builder
    {
        return $query->where('payment_method', self::METHOD_STRIPE);
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_STRIPE => 'Card (Stripe)',
            self::METHOD_MEMBERSHIP => 'Membership',
            self::METHOD_PACK => 'Class Pack',
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_COMP => 'Complimentary',
        ];
    }

    /**
     * Get available manual methods
     */
    public static function getManualMethods(): array
    {
        return [
            self::MANUAL_CASH => 'Cash',
            self::MANUAL_CHECK => 'Check',
            self::MANUAL_VENMO => 'Venmo',
            self::MANUAL_ZELLE => 'Zelle',
            self::MANUAL_PAYPAL => 'PayPal',
            self::MANUAL_CASH_APP => 'Cash App',
            self::MANUAL_BANK_TRANSFER => 'Bank Transfer',
            self::MANUAL_OTHER => 'Other',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
        ];
    }
}
