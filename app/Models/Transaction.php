<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    // Transaction types
    const TYPE_CLASS_BOOKING = 'class_booking';
    const TYPE_SERVICE_BOOKING = 'service_booking';
    const TYPE_MEMBERSHIP_PURCHASE = 'membership_purchase';
    const TYPE_CLASS_PACK_PURCHASE = 'class_pack_purchase';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    const STATUS_CANCELLED = 'cancelled';

    // Payment method constants
    const METHOD_STRIPE = 'stripe';
    const METHOD_MANUAL = 'manual';
    const METHOD_MEMBERSHIP = 'membership';
    const METHOD_PACK = 'pack';
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

    protected $fillable = [
        'host_id',
        'client_id',
        'booking_id',
        'transaction_id',
        'type',
        'purchasable_type',
        'purchasable_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'payment_method',
        'manual_method',
        'stripe_payment_intent_id',
        'stripe_checkout_session_id',
        'stripe_charge_id',
        'refunded_amount',
        'refund_reason',
        'refunded_at',
        'metadata',
        'notes',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = self::generateTransactionId();
            }
        });
    }

    /**
     * Generate a unique transaction ID (TXN_ULID format)
     */
    public static function generateTransactionId(): string
    {
        return 'TXN_' . strtoupper(Str::ulid()->toBase32());
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

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Accessors
     */
    public function getFormattedTotalAttribute(): string
    {
        $symbol = match ($this->currency) {
            'USD', 'CAD', 'AUD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'INR' => '₹',
            default => '$',
        };
        return $symbol . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getIsRefundedAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    public function getIsManualPaymentAttribute(): bool
    {
        return $this->payment_method === self::METHOD_MANUAL;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method === self::METHOD_MANUAL && $this->manual_method) {
            return self::getManualMethods()[$this->manual_method] ?? ucfirst($this->manual_method);
        }
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method ?? 'Unknown');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'badge-success',
            self::STATUS_PENDING, self::STATUS_AUTHORIZED => 'badge-warning',
            self::STATUS_FAILED, self::STATUS_CANCELLED => 'badge-error',
            self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED => 'badge-info',
            default => 'badge-neutral',
        };
    }

    /**
     * Mark transaction as paid
     */
    public function markPaid(?string $stripeChargeId = null): self
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'stripe_charge_id' => $stripeChargeId,
        ]);

        return $this->fresh();
    }

    /**
     * Mark transaction as failed
     */
    public function markFailed(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" : '') . "Failed: {$reason}" : $this->notes,
        ]);

        return $this->fresh();
    }

    /**
     * Mark transaction as cancelled
     */
    public function markCancelled(?string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" : '') . "Cancelled: {$reason}" : $this->notes,
        ]);

        return $this->fresh();
    }

    /**
     * Process a refund
     */
    public function refund(float $amount, ?string $reason = null): self
    {
        $newRefundedAmount = ($this->refunded_amount ?? 0) + $amount;
        $isFullRefund = $newRefundedAmount >= $this->total_amount;

        $this->update([
            'refunded_amount' => $newRefundedAmount,
            'refund_reason' => $reason ?? $this->refund_reason,
            'refunded_at' => now(),
            'status' => $isFullRefund ? self::STATUS_REFUNDED : self::STATUS_PARTIALLY_REFUNDED,
        ]);

        return $this->fresh();
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Get available transaction types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CLASS_BOOKING => 'Class Booking',
            self::TYPE_SERVICE_BOOKING => 'Service Booking',
            self::TYPE_MEMBERSHIP_PURCHASE => 'Membership Purchase',
            self::TYPE_CLASS_PACK_PURCHASE => 'Class Pack Purchase',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_STRIPE => 'Card (Stripe)',
            self::METHOD_MANUAL => 'Manual',
            self::METHOD_MEMBERSHIP => 'Membership Credits',
            self::METHOD_PACK => 'Class Pack',
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
            self::STATUS_AUTHORIZED => 'Authorized',
            self::STATUS_PAID => 'Paid',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PARTIALLY_REFUNDED => 'Partially Refunded',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
