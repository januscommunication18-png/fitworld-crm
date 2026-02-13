<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ClassPack extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'host_id',
        'name',
        'description',
        'class_count',
        'price',
        'expires_after_days',
        'eligible_class_plan_ids',
        'stripe_product_id',
        'stripe_price_id',
        'status',
        'visibility_public',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'class_count' => 'integer',
            'price' => 'decimal:2',
            'expires_after_days' => 'integer',
            'eligible_class_plan_ids' => 'array',
            'visibility_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ClassPackPurchase::class);
    }

    /**
     * Accessors
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getPricePerClassAttribute(): float
    {
        if ($this->class_count <= 0) {
            return 0;
        }
        return round($this->price / $this->class_count, 2);
    }

    public function getFormattedPricePerClassAttribute(): string
    {
        return '$' . number_format($this->price_per_class, 2);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getHasExpirationAttribute(): bool
    {
        return $this->expires_after_days !== null && $this->expires_after_days > 0;
    }

    public function getCoversAllClassesAttribute(): bool
    {
        return empty($this->eligible_class_plan_ids);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_ARCHIVED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    /**
     * Check if pack covers a specific class plan
     */
    public function coversClassPlan(ClassPlan $classPlan): bool
    {
        if ($this->covers_all_classes) {
            return true;
        }

        return in_array($classPlan->id, $this->eligible_class_plan_ids ?? []);
    }

    /**
     * Calculate expiration date from purchase date
     */
    public function calculateExpirationDate(\DateTime $purchaseDate = null): ?\DateTime
    {
        if (!$this->has_expiration) {
            return null;
        }

        $date = $purchaseDate ? clone $purchaseDate : now();
        return $date->addDays($this->expires_after_days);
    }

    /**
     * Archive the pack
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Restore the pack
     */
    public function restore(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Scopes
     */
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('visibility_public', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}
