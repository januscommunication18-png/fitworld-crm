<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CustomerMembership extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    // Payment method constants
    const PAYMENT_STRIPE = 'stripe';
    const PAYMENT_MANUAL = 'manual';

    protected $fillable = [
        'host_id',
        'client_id',
        'membership_plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'payment_method',
        'credits_remaining',
        'credits_per_period',
        'current_period_start',
        'current_period_end',
        'started_at',
        'cancelled_at',
        'paused_at',
        'expires_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'credits_remaining' => 'integer',
            'credits_per_period' => 'integer',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'started_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'paused_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_membership_id');
    }

    public function payments(): HasMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsPausedAttribute(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }
        return false;
    }

    public function getIsStripeAttribute(): bool
    {
        return $this->payment_method === self::PAYMENT_STRIPE;
    }

    public function getIsManualAttribute(): bool
    {
        return $this->payment_method === self::PAYMENT_MANUAL;
    }

    public function getHasCreditsAttribute(): bool
    {
        return $this->credits_remaining !== null && $this->credits_remaining > 0;
    }

    public function getIsUnlimitedAttribute(): bool
    {
        return $this->credits_remaining === null && $this->credits_per_period === null;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_PAUSED => 'badge-warning',
            self::STATUS_CANCELLED => 'badge-error',
            self::STATUS_EXPIRED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    /**
     * Check if membership can be used for a specific class plan
     */
    public function canUseForClassPlan(ClassPlan $classPlan): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_expired) {
            return false;
        }

        // Check if membership plan covers this class
        return $this->membershipPlan->coversClassPlan($classPlan);
    }

    /**
     * Check if membership has available credits
     */
    public function hasAvailableCredits(): bool
    {
        // Unlimited membership
        if ($this->is_unlimited) {
            return true;
        }

        return $this->credits_remaining > 0;
    }

    /**
     * Deduct a credit from the membership
     */
    public function deductCredit(): bool
    {
        if ($this->is_unlimited) {
            return true;
        }

        if ($this->credits_remaining <= 0) {
            return false;
        }

        $this->decrement('credits_remaining');
        return true;
    }

    /**
     * Restore a credit (e.g., when booking is cancelled)
     */
    public function restoreCredit(): void
    {
        if (!$this->is_unlimited) {
            $this->increment('credits_remaining');
        }
    }

    /**
     * Pause the membership
     */
    public function pause(): void
    {
        $this->update([
            'status' => self::STATUS_PAUSED,
            'paused_at' => now(),
        ]);
    }

    /**
     * Resume the membership
     */
    public function resume(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'paused_at' => null,
        ]);
    }

    /**
     * Cancel the membership
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Renew credits for a new period
     */
    public function renewCredits(): void
    {
        if ($this->credits_per_period) {
            $this->update([
                'credits_remaining' => $this->credits_per_period,
                'current_period_start' => now(),
                'current_period_end' => $this->membershipPlan->interval === MembershipPlan::INTERVAL_MONTHLY
                    ? now()->addMonth()
                    : now()->addYear(),
            ]);
        }
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeWithCredits(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('credits_remaining') // Unlimited
              ->orWhere('credits_remaining', '>', 0);
        });
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_STRIPE => 'Stripe',
            self::PAYMENT_MANUAL => 'Manual',
        ];
    }
}
