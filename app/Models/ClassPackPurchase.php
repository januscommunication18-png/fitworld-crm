<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class ClassPackPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'client_id',
        'class_pack_id',
        'classes_remaining',
        'classes_total',
        'purchased_at',
        'expires_at',
        'payment_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'classes_remaining' => 'integer',
            'classes_total' => 'integer',
            'purchased_at' => 'datetime',
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

    public function classPack(): BelongsTo
    {
        return $this->belongsTo(ClassPack::class);
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
        return $this->hasMany(Booking::class, 'class_pack_purchase_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
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

    public function getIsUsableAttribute(): bool
    {
        return $this->has_credits && !$this->is_expired;
    }

    public function getClassesUsedAttribute(): int
    {
        return $this->classes_total - $this->classes_remaining;
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

    /**
     * Check if pack can be used for a specific class plan
     */
    public function canUseForClassPlan(ClassPlan $classPlan): bool
    {
        if (!$this->is_usable) {
            return false;
        }

        return $this->classPack->coversClassPlan($classPlan);
    }

    /**
     * Deduct a class credit
     */
    public function deductCredit(): bool
    {
        if ($this->classes_remaining <= 0) {
            return false;
        }

        $this->decrement('classes_remaining');
        return true;
    }

    /**
     * Restore a class credit (e.g., when booking is cancelled)
     */
    public function restoreCredit(): void
    {
        if ($this->classes_remaining < $this->classes_total) {
            $this->increment('classes_remaining');
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

    public function scopeUsable(Builder $query): Builder
    {
        return $query->withCredits()->notExpired();
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
}
