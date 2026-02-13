<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    use HasFactory;

    // Type constants
    const TYPE_UNLIMITED = 'unlimited';
    const TYPE_CREDITS = 'credits';

    // Interval constants
    const INTERVAL_MONTHLY = 'monthly';
    const INTERVAL_YEARLY = 'yearly';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    // Eligibility scope constants
    const ELIGIBILITY_ALL_CLASSES = 'all_classes';
    const ELIGIBILITY_SELECTED = 'selected_class_plans';

    // Location scope constants
    const LOCATION_ALL = 'all';
    const LOCATION_SELECTED = 'selected';

    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'description',
        'type',
        'price',
        'interval',
        'credits_per_cycle',
        'eligibility_scope',
        'location_scope_type',
        'location_ids',
        'visibility_public',
        'status',
        'stripe_product_id',
        'stripe_price_id',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'location_ids' => 'array',
            'visibility_public' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function classPlans(): BelongsToMany
    {
        return $this->belongsToMany(ClassPlan::class, 'membership_plan_class_plan');
    }

    public function questionnaireAttachments(): MorphMany
    {
        return $this->morphMany(QuestionnaireAttachment::class, 'attachable');
    }

    public function customerMemberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class);
    }

    public function activeCustomerMemberships(): HasMany
    {
        return $this->customerMemberships()->where('status', 'active');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return 'Free';
        }
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted interval
     */
    public function getFormattedIntervalAttribute(): string
    {
        return $this->interval === self::INTERVAL_MONTHLY ? '/month' : '/year';
    }

    /**
     * Get full formatted price with interval
     */
    public function getFormattedPriceWithIntervalAttribute(): string
    {
        return $this->formatted_price . $this->formatted_interval;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_ARCHIVED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_UNLIMITED => 'badge-primary',
            self::TYPE_CREDITS => 'badge-secondary',
            default => 'badge-neutral',
        };
    }

    /**
     * Get formatted type label
     */
    public function getFormattedTypeAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Get formatted eligibility scope
     */
    public function getFormattedEligibilityScopeAttribute(): string
    {
        return self::getEligibilityScopes()[$this->eligibility_scope] ?? $this->eligibility_scope;
    }

    /**
     * Check if this is an unlimited plan
     */
    public function isUnlimited(): bool
    {
        return $this->type === self::TYPE_UNLIMITED;
    }

    /**
     * Check if this is a credits-based plan
     */
    public function isCredits(): bool
    {
        return $this->type === self::TYPE_CREDITS;
    }

    /**
     * Check if plan covers all classes
     */
    public function coversAllClasses(): bool
    {
        return $this->eligibility_scope === self::ELIGIBILITY_ALL_CLASSES;
    }

    /**
     * Check if a class plan is covered by this membership
     */
    public function coversClassPlan(ClassPlan $classPlan): bool
    {
        if ($this->coversAllClasses()) {
            return true;
        }

        return $this->classPlans()->where('class_plan_id', $classPlan->id)->exists();
    }

    /**
     * Scope active plans
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope visible plans (active and public)
     */
    public function scopeVisible($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('visibility_public', true);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope excluding archived
     */
    public function scopeNotArchived($query)
    {
        return $query->where('status', '!=', self::STATUS_ARCHIVED);
    }

    /**
     * Get available types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_UNLIMITED => 'Unlimited',
            self::TYPE_CREDITS => 'Credits-Based',
        ];
    }

    /**
     * Get available intervals
     */
    public static function getIntervals(): array
    {
        return [
            self::INTERVAL_MONTHLY => 'Monthly',
            self::INTERVAL_YEARLY => 'Yearly',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Get available eligibility scopes
     */
    public static function getEligibilityScopes(): array
    {
        return [
            self::ELIGIBILITY_ALL_CLASSES => 'All Classes',
            self::ELIGIBILITY_SELECTED => 'Selected Class Plans Only',
        ];
    }

    /**
     * Get available location scopes
     */
    public static function getLocationScopes(): array
    {
        return [
            self::LOCATION_ALL => 'All Locations',
            self::LOCATION_SELECTED => 'Selected Locations Only',
        ];
    }
}
