<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ClassPassPurchase extends Model
{
    use HasFactory;

    protected $table = 'class_pass_purchases';

    // Activation type constants
    const ACTIVATION_ON_PURCHASE = 'on_purchase';
    const ACTIVATION_ON_FIRST_BOOKING = 'on_first_booking';

    protected $fillable = [
        'host_id',
        'client_id',
        'class_pass_id',
        'classes_remaining',
        'classes_total',
        'credits_used',
        'purchased_at',
        'expires_at',
        'activated_at',
        'activation_type',
        'is_frozen',
        'frozen_at',
        'freeze_expires_at',
        'total_frozen_days',
        'rollover_credits',
        'renewal_count',
        'transferred_from_purchase_id',
        'transferred_to_client_id',
        'transferred_at',
        'stripe_subscription_id',
        'payment_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'classes_remaining' => 'integer',
            'classes_total' => 'integer',
            'credits_used' => 'integer',
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
            'activated_at' => 'datetime',
            'is_frozen' => 'boolean',
            'frozen_at' => 'datetime',
            'freeze_expires_at' => 'datetime',
            'total_frozen_days' => 'integer',
            'rollover_credits' => 'integer',
            'renewal_count' => 'integer',
            'transferred_at' => 'datetime',
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

    public function classPass(): BelongsTo
    {
        return $this->belongsTo(ClassPass::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'class_pass_purchase_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function creditLogs(): HasMany
    {
        return $this->hasMany(ClassPassCreditLog::class);
    }

    public function transferredFromPurchase(): BelongsTo
    {
        return $this->belongsTo(ClassPassPurchase::class, 'transferred_from_purchase_id');
    }

    public function transferredToClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'transferred_to_client_id');
    }

    /**
     * Accessors
     */
    public function getHasCreditsAttribute(): bool
    {
        return $this->classes_remaining > 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Method alias for is_expired attribute
     */
    public function isExpired(): bool
    {
        return $this->is_expired;
    }

    public function getIsUsableAttribute(): bool
    {
        return $this->has_credits && !$this->is_expired && !$this->is_frozen && $this->is_activated;
    }

    public function getIsActivatedAttribute(): bool
    {
        return $this->activated_at !== null;
    }

    public function getIsPendingActivationAttribute(): bool
    {
        return $this->activation_type === self::ACTIVATION_ON_FIRST_BOOKING && !$this->is_activated;
    }

    public function getClassesUsedAttribute(): int
    {
        return $this->classes_total - $this->classes_remaining;
    }

    /**
     * Alias for classes_total
     */
    public function getTotalClassesAttribute(): int
    {
        return $this->classes_total;
    }

    public function getUsagePercentAttribute(): int
    {
        if ($this->classes_total <= 0) {
            return 0;
        }
        return round(($this->classes_used / $this->classes_total) * 100);
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        return now()->diffInDays($this->expires_at, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_until_expiration;
        return $days !== null && $days > 0 && $days <= 7;
    }

    public function getIsInGracePeriodAttribute(): bool
    {
        if (!$this->is_expired || !$this->classPass) {
            return false;
        }

        $gracePeriod = $this->classPass->grace_period_days ?? 0;
        if ($gracePeriod <= 0) {
            return false;
        }

        $gracePeriodEnd = $this->expires_at->copy()->addDays($gracePeriod);
        return now()->lte($gracePeriodEnd);
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_frozen) {
            return 'frozen';
        }
        if ($this->is_pending_activation) {
            return 'pending_activation';
        }
        if ($this->is_expired && $this->is_in_grace_period) {
            return 'grace_period';
        }
        if ($this->is_expired) {
            return 'expired';
        }
        if (!$this->has_credits) {
            return 'exhausted';
        }
        return 'active';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'frozen' => 'badge-warning',
            'pending_activation' => 'badge-info',
            'grace_period' => 'badge-warning',
            'expired' => 'badge-error',
            'exhausted' => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'frozen' => 'Frozen',
            'pending_activation' => 'Pending Activation',
            'grace_period' => 'Grace Period',
            'expired' => 'Expired',
            'exhausted' => 'Exhausted',
            default => 'Unknown',
        };
    }

    public function getIsTransferredAttribute(): bool
    {
        return $this->transferred_to_client_id !== null;
    }

    public function getCanFreezeAttribute(): bool
    {
        if (!$this->classPass || !$this->classPass->allow_freeze) {
            return false;
        }
        return $this->is_activated && !$this->is_frozen && !$this->is_expired;
    }

    public function getCanUnfreezeAttribute(): bool
    {
        return $this->is_frozen;
    }

    public function getCanTransferAttribute(): bool
    {
        if (!$this->classPass || !$this->classPass->allow_transfer) {
            return false;
        }
        return $this->is_usable && !$this->is_transferred;
    }

    public function getRemainingFreezeDaysAttribute(): int
    {
        if (!$this->classPass) {
            return 0;
        }
        return max(0, ($this->classPass->max_freeze_days ?? 30) - $this->total_frozen_days);
    }

    /**
     * Check if pass can be used for a specific class session
     */
    public function canUseForClassSession(ClassSession $session): bool
    {
        if (!$this->is_usable) {
            return false;
        }

        return $this->classPass->coversClassSession($session);
    }

    /**
     * Check if pass can be used for a specific class plan
     */
    public function canUseForClassPlan(ClassPlan $classPlan): bool
    {
        if (!$this->is_usable) {
            return false;
        }

        return $this->classPass->coversClassPlan($classPlan);
    }

    /**
     * Calculate credits required for a session
     */
    public function calculateCreditsForSession(ClassSession $session): int
    {
        return $this->classPass->calculateCreditsForSession($session);
    }

    /**
     * Deduct credits (with logging)
     */
    public function deductCredits(int $credits = 1, ?Booking $booking = null, ?int $userId = null): bool
    {
        if ($this->classes_remaining < $credits) {
            return false;
        }

        $this->decrement('classes_remaining', $credits);
        $this->increment('credits_used', $credits);

        // Log the deduction
        $this->creditLogs()->create([
            'booking_id' => $booking?->id,
            'credits_change' => -$credits,
            'credit_type' => 'booking',
            'notes' => $booking ? "Deducted for booking #{$booking->id}" : 'Manual deduction',
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Restore credits (e.g., when booking is cancelled)
     */
    public function restoreCredits(int $credits = 1, ?Booking $booking = null, ?int $userId = null): void
    {
        if ($this->classes_remaining + $credits > $this->classes_total + $this->rollover_credits) {
            $credits = ($this->classes_total + $this->rollover_credits) - $this->classes_remaining;
        }

        if ($credits > 0) {
            $this->increment('classes_remaining', $credits);
            $this->decrement('credits_used', min($credits, $this->credits_used));

            // Log the restoration
            $this->creditLogs()->create([
                'booking_id' => $booking?->id,
                'credits_change' => $credits,
                'credit_type' => 'cancellation_restore',
                'notes' => $booking ? "Restored from cancelled booking #{$booking->id}" : 'Manual restoration',
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);
        }
    }

    /**
     * Admin credit adjustment
     */
    public function adjustCredits(int $adjustment, string $reason, ?int $userId = null): void
    {
        $newBalance = max(0, $this->classes_remaining + $adjustment);
        $actualAdjustment = $newBalance - $this->classes_remaining;

        $this->update(['classes_remaining' => $newBalance]);

        // Log the adjustment
        $this->creditLogs()->create([
            'credits_change' => $actualAdjustment,
            'credit_type' => 'admin_adjust',
            'notes' => $reason,
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Activate the pass
     */
    public function activate(): void
    {
        if ($this->is_activated) {
            return;
        }

        $activationDate = now();
        $expiresAt = $this->classPass->calculateExpirationDate($activationDate);

        $this->update([
            'activated_at' => $activationDate,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Freeze the pass
     */
    public function freeze(?int $days = null, ?int $userId = null): bool
    {
        if (!$this->can_freeze) {
            return false;
        }

        $maxDays = $this->remaining_freeze_days;
        $freezeDays = $days ? min($days, $maxDays) : $maxDays;

        if ($freezeDays <= 0) {
            return false;
        }

        $this->update([
            'is_frozen' => true,
            'frozen_at' => now(),
            'freeze_expires_at' => now()->addDays($freezeDays),
        ]);

        // Log the freeze
        $this->creditLogs()->create([
            'credits_change' => 0,
            'credit_type' => 'freeze_adjust',
            'notes' => "Pass frozen for {$freezeDays} days",
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Unfreeze the pass
     */
    public function unfreeze(?int $userId = null): bool
    {
        if (!$this->is_frozen) {
            return false;
        }

        // Calculate days frozen
        $daysFrozen = $this->frozen_at->diffInDays(now());

        // Extend expiration by days frozen
        if ($this->expires_at) {
            $this->expires_at = $this->expires_at->addDays($daysFrozen);
        }

        $this->update([
            'is_frozen' => false,
            'frozen_at' => null,
            'freeze_expires_at' => null,
            'total_frozen_days' => $this->total_frozen_days + $daysFrozen,
            'expires_at' => $this->expires_at,
        ]);

        // Log the unfreeze
        $this->creditLogs()->create([
            'credits_change' => 0,
            'credit_type' => 'freeze_adjust',
            'notes' => "Pass unfrozen after {$daysFrozen} days. Expiration extended.",
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Extend the pass expiration
     */
    public function extend(int $days, ?int $userId = null): void
    {
        if (!$this->expires_at) {
            return;
        }

        $oldExpiry = $this->expires_at->copy();
        $this->update([
            'expires_at' => $this->expires_at->addDays($days),
        ]);

        // Log the extension
        $this->creditLogs()->create([
            'credits_change' => 0,
            'credit_type' => 'admin_adjust',
            'notes' => "Expiration extended by {$days} days (from {$oldExpiry->format('Y-m-d')} to {$this->expires_at->format('Y-m-d')})",
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Process rollover of unused credits
     */
    public function processRollover(int $maxCredits, int $maxPeriods): int
    {
        if ($this->renewal_count >= $maxPeriods) {
            return 0;
        }

        $rolloverAmount = min($this->classes_remaining, $maxCredits);

        if ($rolloverAmount > 0) {
            $this->creditLogs()->create([
                'credits_change' => $rolloverAmount,
                'credit_type' => 'rollover',
                'notes' => "Rolled over {$rolloverAmount} credits to next period",
                'created_by_user_id' => null,
            ]);
        }

        return $rolloverAmount;
    }

    /**
     * Transfer remaining credits to another client
     */
    public function transferTo(Client $targetClient, ?int $userId = null): ?ClassPassPurchase
    {
        if (!$this->can_transfer) {
            return null;
        }

        // Create new purchase for target client
        $newPurchase = static::create([
            'host_id' => $this->host_id,
            'client_id' => $targetClient->id,
            'class_pass_id' => $this->class_pass_id,
            'classes_remaining' => $this->classes_remaining,
            'classes_total' => $this->classes_remaining, // Only transfer remaining
            'credits_used' => 0,
            'purchased_at' => now(),
            'activated_at' => now(),
            'activation_type' => self::ACTIVATION_ON_PURCHASE,
            'expires_at' => $this->expires_at, // Keep same expiration
            'transferred_from_purchase_id' => $this->id,
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        // Log transfer out
        $this->creditLogs()->create([
            'credits_change' => -$this->classes_remaining,
            'credit_type' => 'transfer_out',
            'notes' => "Transferred {$this->classes_remaining} credits to client #{$targetClient->id}",
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        // Log transfer in
        $newPurchase->creditLogs()->create([
            'credits_change' => $newPurchase->classes_remaining,
            'credit_type' => 'transfer_in',
            'notes' => "Received {$newPurchase->classes_remaining} credits from purchase #{$this->id}",
            'created_by_user_id' => $userId ?? auth()->id(),
        ]);

        // Update original purchase
        $this->update([
            'classes_remaining' => 0,
            'transferred_to_client_id' => $targetClient->id,
            'transferred_at' => now(),
        ]);

        return $newPurchase;
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

    public function scopeWithCredits(Builder $query): Builder
    {
        return $query->where('classes_remaining', '>', 0);
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeActivated(Builder $query): Builder
    {
        return $query->whereNotNull('activated_at');
    }

    public function scopePendingActivation(Builder $query): Builder
    {
        return $query->whereNull('activated_at')
                     ->where('activation_type', self::ACTIVATION_ON_FIRST_BOOKING);
    }

    public function scopeNotFrozen(Builder $query): Builder
    {
        return $query->where('is_frozen', false);
    }

    public function scopeFrozen(Builder $query): Builder
    {
        return $query->where('is_frozen', true);
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->withCredits()
                     ->notExpired()
                     ->activated()
                     ->notFrozen()
                     ->whereNull('transferred_to_client_id');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    public function scopeExhausted(Builder $query): Builder
    {
        return $query->where('classes_remaining', '<=', 0);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '>', now())
                     ->where('expires_at', '<=', now()->addDays($days));
    }

    public function scopeNotTransferred(Builder $query): Builder
    {
        return $query->whereNull('transferred_to_client_id');
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->whereNotNull('stripe_subscription_id');
    }
}
