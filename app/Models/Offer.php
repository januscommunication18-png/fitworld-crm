<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ARCHIVED = 'archived';

    // Applies to constants
    const APPLIES_TO_ALL = 'all';
    const APPLIES_TO_CLASSES = 'classes';
    const APPLIES_TO_SERVICES = 'services';
    const APPLIES_TO_MEMBERSHIPS = 'memberships';
    const APPLIES_TO_RETAIL = 'retail';
    const APPLIES_TO_CLASS_PACKS = 'class_packs';
    const APPLIES_TO_SPECIFIC = 'specific';

    // Plan scope constants
    const PLAN_ALL = 'all_plans';
    const PLAN_SPECIFIC = 'specific_plans';
    const PLAN_FIRST_TIME = 'first_time';
    const PLAN_TRIAL = 'trial';
    const PLAN_UPGRADE = 'upgrade';

    // Discount type constants
    const DISCOUNT_PERCENTAGE = 'percentage';
    const DISCOUNT_FIXED = 'fixed_amount';
    const DISCOUNT_BUY_X_GET_Y = 'buy_x_get_y';
    const DISCOUNT_FREE_CLASS = 'free_class';
    const DISCOUNT_FREE_ADDON = 'free_addon';
    const DISCOUNT_BUNDLE = 'bundle';

    // Target audience constants
    const TARGET_ALL = 'all_members';
    const TARGET_SEGMENT = 'specific_segment';
    const TARGET_NEW = 'new_members';
    const TARGET_INACTIVE = 'inactive_members';
    const TARGET_HIGH_SPENDERS = 'high_spenders';
    const TARGET_VIP = 'vip_tier';

    protected $fillable = [
        'host_id',
        'name',
        'code',
        'description',
        'banner_image',
        'internal_notes',
        'status',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'auto_expire',
        'is_recurring',
        'recurring_pattern',
        'recurring_months',
        'applies_to',
        'applicable_item_ids',
        'plan_scope',
        'applicable_plan_ids',
        'discount_type',
        'discount_value',
        'buy_quantity',
        'get_quantity',
        'free_classes',
        'free_addon_ids',
        'discount_amounts',
        'target_audience',
        'segment_id',
        'total_usage_limit',
        'per_member_limit',
        'first_x_users',
        'auto_stop_on_limit',
        'total_redemptions',
        'total_discount_given',
        'total_revenue_generated',
        'online_booking_only',
        'front_desk_only',
        'app_only',
        'manual_override_allowed',
        'can_combine',
        'highest_discount_wins',
        'auto_apply',
        'require_code',
        'show_on_invoice',
        'invoice_line_text',
        'new_members_acquired',
        'conversion_rate',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'auto_expire' => 'boolean',
            'is_recurring' => 'boolean',
            'recurring_months' => 'array',
            'applicable_item_ids' => 'array',
            'applicable_plan_ids' => 'array',
            'discount_value' => 'decimal:2',
            'buy_quantity' => 'integer',
            'get_quantity' => 'integer',
            'free_classes' => 'integer',
            'free_addon_ids' => 'array',
            'discount_amounts' => 'array',
            'total_usage_limit' => 'integer',
            'per_member_limit' => 'integer',
            'first_x_users' => 'integer',
            'auto_stop_on_limit' => 'boolean',
            'total_redemptions' => 'integer',
            'total_discount_given' => 'decimal:2',
            'total_revenue_generated' => 'decimal:2',
            'online_booking_only' => 'boolean',
            'front_desk_only' => 'boolean',
            'app_only' => 'boolean',
            'manual_override_allowed' => 'boolean',
            'can_combine' => 'boolean',
            'highest_discount_wins' => 'boolean',
            'auto_apply' => 'boolean',
            'require_code' => 'boolean',
            'show_on_invoice' => 'boolean',
            'new_members_acquired' => 'integer',
            'conversion_rate' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($offer) {
            // Generate code if not provided and require_code is true
            if ($offer->require_code && empty($offer->code)) {
                $offer->code = strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class);
    }

    // Scopes
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now->toDateString());
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('total_usage_limit')
                    ->orWhereColumn('total_redemptions', '<', 'total_usage_limit');
            });
    }

    public function scopeWithCode(Builder $query): Builder
    {
        return $query->where('require_code', true)->whereNotNull('code');
    }

    public function scopeAutoApply(Builder $query): Builder
    {
        return $query->where('auto_apply', true);
    }

    // Helpers
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_PAUSED => 'badge-warning',
            self::STATUS_EXPIRED => 'badge-error',
            self::STATUS_ARCHIVED => 'badge-neutral',
            default => 'badge-info',
        };
    }

    public static function getAppliesTo(): array
    {
        return [
            self::APPLIES_TO_ALL => 'All Products',
            self::APPLIES_TO_CLASSES => 'Classes Only',
            self::APPLIES_TO_SERVICES => 'Services Only',
            self::APPLIES_TO_MEMBERSHIPS => 'Memberships Only',
            self::APPLIES_TO_RETAIL => 'Retail Only',
            self::APPLIES_TO_CLASS_PACKS => 'Class Packs Only',
            self::APPLIES_TO_SPECIFIC => 'Specific Items',
        ];
    }

    public static function getDiscountTypes(): array
    {
        return [
            self::DISCOUNT_PERCENTAGE => 'Percentage (%)',
            self::DISCOUNT_FIXED => 'Fixed Amount',
            self::DISCOUNT_BUY_X_GET_Y => 'Buy X Get Y',
            self::DISCOUNT_FREE_CLASS => 'Free Class Credits',
            self::DISCOUNT_FREE_ADDON => 'Free Add-on',
            self::DISCOUNT_BUNDLE => 'Bundle Discount',
        ];
    }

    public static function getTargetAudiences(): array
    {
        return [
            self::TARGET_ALL => 'All Members',
            self::TARGET_SEGMENT => 'Specific Segment',
            self::TARGET_NEW => 'New Members Only',
            self::TARGET_INACTIVE => 'Inactive Members',
            self::TARGET_HIGH_SPENDERS => 'High Spenders',
            self::TARGET_VIP => 'VIP Tier',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        if ($this->end_date && $this->end_date->isPast()) {
            return true;
        }
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isAvailable(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $now = Carbon::now();

        // Check date range
        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        // Check usage limit
        if ($this->total_usage_limit && $this->total_redemptions >= $this->total_usage_limit) {
            return false;
        }

        // Check first X users limit
        if ($this->first_x_users && $this->total_redemptions >= $this->first_x_users) {
            return false;
        }

        return true;
    }

    /**
     * Check if client is eligible for this offer
     */
    public function isClientEligible(Client $client): bool
    {
        // Check per-member limit
        if ($this->per_member_limit) {
            $clientRedemptions = $this->redemptions()
                ->where('client_id', $client->id)
                ->whereNotIn('status', ['refunded', 'voided'])
                ->count();

            if ($clientRedemptions >= $this->per_member_limit) {
                return false;
            }
        }

        // Check target audience
        switch ($this->target_audience) {
            case self::TARGET_NEW:
                // New members: created within last 30 days
                if ($client->created_at < Carbon::now()->subDays(30)) {
                    return false;
                }
                break;

            case self::TARGET_INACTIVE:
                // Inactive: no visit in last 30 days
                if ($client->last_visit_at && $client->last_visit_at > Carbon::now()->subDays(30)) {
                    return false;
                }
                break;

            case self::TARGET_HIGH_SPENDERS:
                // High spenders: total spent > $500 (configurable)
                if ($client->total_spent < 500) {
                    return false;
                }
                break;

            case self::TARGET_VIP:
                // VIP tier from scoring
                $score = $client->score;
                if (!$score || $score->loyalty_tier !== 'vip') {
                    return false;
                }
                break;

            case self::TARGET_SEGMENT:
                // Specific segment
                if ($this->segment_id) {
                    $inSegment = $client->segments()
                        ->where('segments.id', $this->segment_id)
                        ->exists();
                    if (!$inSegment) {
                        return false;
                    }
                }
                break;
        }

        return true;
    }

    /**
     * Calculate discount for a given price
     */
    public function calculateDiscount(float $originalPrice, string $currency = 'USD'): float
    {
        switch ($this->discount_type) {
            case self::DISCOUNT_PERCENTAGE:
                return round($originalPrice * ($this->discount_value / 100), 2);

            case self::DISCOUNT_FIXED:
                // Check for multi-currency
                if ($this->discount_amounts && isset($this->discount_amounts[$currency])) {
                    return min($this->discount_amounts[$currency], $originalPrice);
                }
                return min($this->discount_value ?? 0, $originalPrice);

            case self::DISCOUNT_FREE_CLASS:
            case self::DISCOUNT_FREE_ADDON:
                // These don't reduce price directly
                return 0;

            default:
                return 0;
        }
    }

    /**
     * Get formatted discount display
     */
    public function getFormattedDiscount(): string
    {
        switch ($this->discount_type) {
            case self::DISCOUNT_PERCENTAGE:
                return number_format($this->discount_value, 0) . '% off';
            case self::DISCOUNT_FIXED:
                return '$' . number_format($this->discount_value, 2) . ' off';
            case self::DISCOUNT_BUY_X_GET_Y:
                return "Buy {$this->buy_quantity} Get {$this->get_quantity}";
            case self::DISCOUNT_FREE_CLASS:
                return "{$this->free_classes} Free Class(es)";
            case self::DISCOUNT_FREE_ADDON:
                return 'Free Add-on';
            case self::DISCOUNT_BUNDLE:
                return 'Bundle Discount';
            default:
                return 'Discount';
        }
    }

    /**
     * Update analytics after redemption
     */
    public function recordRedemption(float $discountAmount, float $revenueGenerated, bool $isNewMember = false): void
    {
        $this->increment('total_redemptions');
        $this->increment('total_discount_given', $discountAmount);
        $this->increment('total_revenue_generated', $revenueGenerated);

        if ($isNewMember) {
            $this->increment('new_members_acquired');
        }

        // Calculate conversion rate
        if ($this->total_redemptions > 0) {
            // This is a simplified conversion rate - could be enhanced with view tracking
            $this->conversion_rate = min(100, ($this->total_redemptions / max(1, $this->total_redemptions * 10)) * 100);
            $this->save();
        }

        // Check if we should auto-stop
        if ($this->auto_stop_on_limit) {
            if ($this->total_usage_limit && $this->total_redemptions >= $this->total_usage_limit) {
                $this->update(['status' => self::STATUS_EXPIRED]);
            }
        }
    }
}
