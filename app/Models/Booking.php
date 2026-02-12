<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Booking extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_COMPLETED = 'completed';

    // Payment method constants
    const PAYMENT_STRIPE = 'stripe';
    const PAYMENT_MEMBERSHIP = 'membership';
    const PAYMENT_PACK = 'pack';
    const PAYMENT_MANUAL = 'manual';
    const PAYMENT_CASH = 'cash';

    protected $fillable = [
        'host_id',
        'client_id',
        'bookable_type',
        'bookable_id',
        'status',
        'payment_method',
        'membership_id',
        'price_paid',
        'credits_used',
        'booked_at',
        'cancelled_at',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
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

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get formatted price paid
     */
    public function getFormattedPricePaidAttribute(): string
    {
        if ($this->price_paid === null) {
            return 'Free';
        }
        return '$' . number_format($this->price_paid, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'badge-success',
            self::STATUS_COMPLETED => 'badge-info',
            self::STATUS_CANCELLED => 'badge-neutral',
            self::STATUS_NO_SHOW => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Get payment method badge class
     */
    public function getPaymentMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_STRIPE => 'badge-primary',
            self::PAYMENT_MEMBERSHIP => 'badge-secondary',
            self::PAYMENT_PACK => 'badge-accent',
            self::PAYMENT_MANUAL => 'badge-info',
            self::PAYMENT_CASH => 'badge-warning',
            default => 'badge-neutral',
        };
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if booking was no-show
     */
    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if booking is checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Scope for host
     */
    public function scopeForHost($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Scope for client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope no-show bookings
     */
    public function scopeNoShow($query)
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    /**
     * Scope active bookings (confirmed or completed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_COMPLETED]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_STRIPE => 'Card (Stripe)',
            self::PAYMENT_MEMBERSHIP => 'Membership',
            self::PAYMENT_PACK => 'Class Pack',
            self::PAYMENT_MANUAL => 'Manual',
            self::PAYMENT_CASH => 'Cash',
        ];
    }
}
