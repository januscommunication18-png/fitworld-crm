<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ClassPass extends Model
{
    use HasFactory;

    protected $table = 'class_passes';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    // Activation type constants
    const ACTIVATION_ON_PURCHASE = 'on_purchase';
    const ACTIVATION_ON_FIRST_BOOKING = 'on_first_booking';

    // Eligibility type constants
    const ELIGIBILITY_ALL = 'all';
    const ELIGIBILITY_ALL_CLASSES_AND_SERVICES = 'all_classes_and_services';
    const ELIGIBILITY_CATEGORIES = 'categories';
    const ELIGIBILITY_CLASS_PLANS = 'class_plans';
    const ELIGIBILITY_SERVICE_PLANS = 'service_plans';
    const ELIGIBILITY_INSTRUCTORS = 'instructors';
    const ELIGIBILITY_LOCATIONS = 'locations';

    // Validity type constants
    const VALIDITY_DAYS = 'days';
    const VALIDITY_MONTHS = 'months';
    const VALIDITY_NO_EXPIRATION = 'no_expiration';

    // Renewal interval constants
    const RENEWAL_WEEKLY = 'weekly';
    const RENEWAL_BI_WEEKLY = 'bi_weekly';
    const RENEWAL_MONTHLY = 'monthly';

    // Class type constants (for exclusions)
    const CLASS_TYPE_GROUP = 'group';
    const CLASS_TYPE_WORKSHOP = 'workshop';
    const CLASS_TYPE_SPECIAL_EVENT = 'special_event';
    const CLASS_TYPE_MASTERCLASS = 'masterclass';
    const CLASS_TYPE_PRIVATE = 'private';

    protected $fillable = [
        'host_id',
        'name',
        'description',
        'class_count',
        'price',
        'prices',
        'new_member_prices',
        'expires_after_days',
        'activation_type',
        'eligibility_type',
        'eligible_class_plan_ids',
        'eligible_categories',
        'eligible_instructor_ids',
        'eligible_location_ids',
        'eligible_service_plan_ids',
        'excluded_class_types',
        'default_credits_per_class',
        'credit_rules',
        'peak_time_multiplier',
        'peak_time_days',
        'peak_time_start',
        'peak_time_end',
        'validity_type',
        'validity_value',
        'grace_period_days',
        'allow_admin_extension',
        'allow_freeze',
        'max_freeze_days',
        'reactivation_fee',
        'reactivation_fee_prices',
        'allow_transfer',
        'allow_family_sharing',
        'allow_gifting',
        'max_family_members',
        'is_recurring',
        'renewal_interval',
        'rollover_enabled',
        'max_rollover_credits',
        'max_rollover_periods',
        'color',
        'image_path',
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
            'prices' => 'array',
            'new_member_prices' => 'array',
            'expires_after_days' => 'integer',
            'eligible_class_plan_ids' => 'array',
            'eligible_categories' => 'array',
            'eligible_instructor_ids' => 'array',
            'eligible_location_ids' => 'array',
            'eligible_service_plan_ids' => 'array',
            'excluded_class_types' => 'array',
            'default_credits_per_class' => 'integer',
            'credit_rules' => 'array',
            'peak_time_multiplier' => 'decimal:2',
            'peak_time_days' => 'array',
            'peak_time_start' => 'datetime:H:i',
            'peak_time_end' => 'datetime:H:i',
            'validity_value' => 'integer',
            'grace_period_days' => 'integer',
            'allow_admin_extension' => 'boolean',
            'allow_freeze' => 'boolean',
            'max_freeze_days' => 'integer',
            'reactivation_fee' => 'decimal:2',
            'reactivation_fee_prices' => 'array',
            'allow_transfer' => 'boolean',
            'allow_family_sharing' => 'boolean',
            'allow_gifting' => 'boolean',
            'max_family_members' => 'integer',
            'is_recurring' => 'boolean',
            'rollover_enabled' => 'boolean',
            'max_rollover_credits' => 'integer',
            'max_rollover_periods' => 'integer',
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
        return $this->hasMany(ClassPassPurchase::class);
    }

    public function activePurchases(): HasMany
    {
        return $this->purchases()->usable();
    }

    /**
     * Get eligible class plans (if eligibility_type is class_plans)
     */
    public function eligibleClassPlans()
    {
        if ($this->eligibility_type !== self::ELIGIBILITY_CLASS_PLANS || empty($this->eligible_class_plan_ids)) {
            return collect();
        }

        return ClassPlan::whereIn('id', $this->eligible_class_plan_ids)->get();
    }

    /**
     * Get eligible instructors (if eligibility_type is instructors)
     */
    public function eligibleInstructors()
    {
        if ($this->eligibility_type !== self::ELIGIBILITY_INSTRUCTORS || empty($this->eligible_instructor_ids)) {
            return collect();
        }

        return Instructor::whereIn('id', $this->eligible_instructor_ids)->get();
    }

    /**
     * Get eligible locations (if eligibility_type is locations)
     */
    public function eligibleLocations()
    {
        if ($this->eligibility_type !== self::ELIGIBILITY_LOCATIONS || empty($this->eligible_location_ids)) {
            return collect();
        }

        return Location::whereIn('id', $this->eligible_location_ids)->get();
    }

    /**
     * Price helpers
     */
    public function getPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->prices) && isset($this->prices[$currency])) {
            return (float) $this->prices[$currency];
        }

        return $this->price !== null ? (float) $this->price : null;
    }

    public function getFormattedPriceForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $price = $this->getPriceForCurrency($currency);

        if ($price === null) {
            return 'N/A';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    public function hasPriceForCurrency(string $currency): bool
    {
        return !empty($this->prices) && isset($this->prices[$currency]) && $this->prices[$currency] !== null;
    }

    public function getNewMemberPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->new_member_prices) && isset($this->new_member_prices[$currency])) {
            return (float) $this->new_member_prices[$currency];
        }

        return null;
    }

    public function getReactivationFeeForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->reactivation_fee_prices) && isset($this->reactivation_fee_prices[$currency])) {
            return (float) $this->reactivation_fee_prices[$currency];
        }

        return $this->reactivation_fee !== null ? (float) $this->reactivation_fee : 0;
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
        return $this->validity_type !== self::VALIDITY_NO_EXPIRATION;
    }

    public function getCoversAllClassesAttribute(): bool
    {
        return $this->eligibility_type === self::ELIGIBILITY_ALL || $this->eligibility_type === self::ELIGIBILITY_ALL_CLASSES_AND_SERVICES;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_ACTIVE => 'badge-success',
            self::STATUS_ARCHIVED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    public function getFormattedValidityAttribute(): string
    {
        return match ($this->validity_type) {
            self::VALIDITY_NO_EXPIRATION => 'No Expiration',
            self::VALIDITY_DAYS => $this->validity_value . ' days',
            self::VALIDITY_MONTHS => $this->validity_value . ' ' . ($this->validity_value == 1 ? 'month' : 'months'),
            default => 'N/A',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk(config('filesystems.uploads'))->url($this->image_path);
    }

    public function getValidityDisplayAttribute(): string
    {
        return match ($this->validity_type) {
            self::VALIDITY_NO_EXPIRATION => 'No Expiration',
            self::VALIDITY_DAYS => $this->validity_value . ' days',
            self::VALIDITY_MONTHS => $this->validity_value . ' ' . ($this->validity_value == 1 ? 'month' : 'months'),
            default => 'N/A',
        };
    }

    public function getActivationTypeDisplayAttribute(): string
    {
        return match ($this->activation_type) {
            self::ACTIVATION_ON_PURCHASE => 'Starts on purchase',
            self::ACTIVATION_ON_FIRST_BOOKING => 'Starts on first class booked',
            default => 'Unknown',
        };
    }

    public function getEligibilityDisplayAttribute(): string
    {
        return match ($this->eligibility_type) {
            self::ELIGIBILITY_ALL => 'All Classes',
            self::ELIGIBILITY_CATEGORIES => 'Selected Categories',
            self::ELIGIBILITY_CLASS_PLANS => 'Selected Classes',
            self::ELIGIBILITY_INSTRUCTORS => 'Selected Instructors',
            self::ELIGIBILITY_LOCATIONS => 'Selected Locations',
            default => 'Unknown',
        };
    }

    public function getHasPeakTimeRulesAttribute(): bool
    {
        return $this->peak_time_multiplier !== null && $this->peak_time_multiplier > 0;
    }

    public function getHasVariableCreditRulesAttribute(): bool
    {
        return !empty($this->credit_rules);
    }

    /**
     * Check if pass covers a specific class session
     */
    public function coversClassSession(ClassSession $session): bool
    {
        // Check excluded class types first
        if (!empty($this->excluded_class_types)) {
            $classPlan = $session->classPlan;
            if ($classPlan && in_array($classPlan->type, $this->excluded_class_types)) {
                return false;
            }
        }

        // Check based on eligibility type
        return match ($this->eligibility_type) {
            self::ELIGIBILITY_ALL => true,
            self::ELIGIBILITY_CLASS_PLANS => $this->coversClassPlan($session->classPlan),
            self::ELIGIBILITY_CATEGORIES => $this->coversCategory($session->classPlan?->category),
            self::ELIGIBILITY_INSTRUCTORS => $this->coversInstructor($session->primary_instructor_id),
            self::ELIGIBILITY_LOCATIONS => $this->coversLocation($session->location_id),
            default => false,
        };
    }

    /**
     * Check if pass covers a specific class plan
     */
    public function coversClassPlan(?ClassPlan $classPlan): bool
    {
        if (!$classPlan) {
            return false;
        }

        // Check excluded types
        if (!empty($this->excluded_class_types) && in_array($classPlan->type, $this->excluded_class_types)) {
            return false;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_ALL || $this->eligibility_type === self::ELIGIBILITY_ALL_CLASSES_AND_SERVICES) {
            return true;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_CLASS_PLANS) {
            return !empty($this->eligible_class_plan_ids) && in_array($classPlan->id, $this->eligible_class_plan_ids);
        }

        if ($this->eligibility_type === self::ELIGIBILITY_CATEGORIES) {
            return $this->coversCategory($classPlan->category);
        }

        return false;
    }

    /**
     * Check if pass covers a specific service plan
     */
    public function coversServicePlan(?\App\Models\ServicePlan $servicePlan): bool
    {
        if (!$servicePlan) {
            return false;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_ALL_CLASSES_AND_SERVICES) {
            return true;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_SERVICE_PLANS) {
            return !empty($this->eligible_service_plan_ids) && in_array($servicePlan->id, $this->eligible_service_plan_ids);
        }

        return false;
    }

    /**
     * Check if pass covers a specific category
     */
    public function coversCategory(?string $category): bool
    {
        if (!$category) {
            return false;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_ALL) {
            return true;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_CATEGORIES) {
            return !empty($this->eligible_categories) && in_array($category, $this->eligible_categories);
        }

        return false;
    }

    /**
     * Check if pass covers a specific instructor
     */
    public function coversInstructor(?int $instructorId): bool
    {
        if (!$instructorId) {
            return false;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_ALL) {
            return true;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_INSTRUCTORS) {
            return !empty($this->eligible_instructor_ids) && in_array($instructorId, $this->eligible_instructor_ids);
        }

        return false;
    }

    /**
     * Check if pass covers a specific location
     */
    public function coversLocation(?int $locationId): bool
    {
        if (!$locationId) {
            return false;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_ALL) {
            return true;
        }

        if ($this->eligibility_type === self::ELIGIBILITY_LOCATIONS) {
            return !empty($this->eligible_location_ids) && in_array($locationId, $this->eligible_location_ids);
        }

        return false;
    }

    /**
     * Calculate credits required for a class session
     */
    public function calculateCreditsForSession(ClassSession $session): int
    {
        $credits = $this->default_credits_per_class;

        // Check credit rules
        if (!empty($this->credit_rules)) {
            foreach ($this->credit_rules as $rule) {
                $ruleCredits = $this->matchCreditRule($rule, $session);
                if ($ruleCredits !== null) {
                    $credits = $ruleCredits;
                    break; // First matching rule wins
                }
            }
        }

        // Apply peak time multiplier
        if ($this->has_peak_time_rules && $this->isPeakTime($session)) {
            $credits = (int) ceil($credits * $this->peak_time_multiplier);
        }

        return max(1, $credits); // Minimum 1 credit
    }

    /**
     * Match a credit rule against a session
     */
    protected function matchCreditRule(array $rule, ClassSession $session): ?int
    {
        $classPlan = $session->classPlan;

        // Rule by class plan ID
        if (isset($rule['class_plan_id']) && $classPlan && $rule['class_plan_id'] == $classPlan->id) {
            return (int) ($rule['credits'] ?? 1);
        }

        // Rule by category
        if (isset($rule['category']) && $classPlan && $rule['category'] === $classPlan->category) {
            return (int) ($rule['credits'] ?? 1);
        }

        // Rule by class type
        if (isset($rule['type']) && $classPlan && $rule['type'] === $classPlan->type) {
            return (int) ($rule['credits'] ?? 1);
        }

        // Rule by instructor
        if (isset($rule['instructor_id']) && $rule['instructor_id'] == $session->primary_instructor_id) {
            return (int) ($rule['credits'] ?? 1);
        }

        return null;
    }

    /**
     * Check if a session falls in peak time
     */
    public function isPeakTime(ClassSession $session): bool
    {
        if (!$this->has_peak_time_rules) {
            return false;
        }

        $sessionTime = Carbon::parse($session->start_time);

        // Check day of week (0 = Sunday, 6 = Saturday)
        if (!empty($this->peak_time_days)) {
            $dayOfWeek = $sessionTime->dayOfWeek;
            if (!in_array($dayOfWeek, $this->peak_time_days)) {
                return false;
            }
        }

        // Check time range
        if ($this->peak_time_start && $this->peak_time_end) {
            $timeOnly = $sessionTime->format('H:i:s');
            $start = Carbon::parse($this->peak_time_start)->format('H:i:s');
            $end = Carbon::parse($this->peak_time_end)->format('H:i:s');

            return $timeOnly >= $start && $timeOnly <= $end;
        }

        return true; // Day matches, no time restriction
    }

    /**
     * Calculate expiration date from activation date
     */
    public function calculateExpirationDate(?\DateTime $activationDate = null): ?\DateTime
    {
        if ($this->validity_type === self::VALIDITY_NO_EXPIRATION) {
            return null;
        }

        $date = $activationDate ? Carbon::parse($activationDate) : now();

        return match ($this->validity_type) {
            self::VALIDITY_DAYS => $date->addDays($this->validity_value ?? 30),
            self::VALIDITY_MONTHS => $date->addMonths($this->validity_value ?? 1),
            default => null,
        };
    }

    /**
     * Archive the pass
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Restore the pass
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

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', true);
    }

    public function scopeNonRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring', false);
    }

    /**
     * Static helpers
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    public static function getActivationTypes(): array
    {
        return [
            self::ACTIVATION_ON_PURCHASE => 'Starts on purchase',
            self::ACTIVATION_ON_FIRST_BOOKING => 'Starts on first class booked',
        ];
    }

    public static function getEligibilityTypes(): array
    {
        return [
            self::ELIGIBILITY_ALL => 'All Classes',
            self::ELIGIBILITY_ALL_CLASSES_AND_SERVICES => 'All Classes & Services',
            self::ELIGIBILITY_CATEGORIES => 'Selected Categories',
            self::ELIGIBILITY_CLASS_PLANS => 'Selected Classes',
            self::ELIGIBILITY_SERVICE_PLANS => 'Selected Services',
            self::ELIGIBILITY_INSTRUCTORS => 'Selected Instructors',
            self::ELIGIBILITY_LOCATIONS => 'Selected Locations',
        ];
    }

    public static function getValidityTypes(): array
    {
        return [
            self::VALIDITY_DAYS => 'Days',
            self::VALIDITY_MONTHS => 'Months',
            self::VALIDITY_NO_EXPIRATION => 'No Expiration',
        ];
    }

    public static function getRenewalIntervals(): array
    {
        return [
            self::RENEWAL_WEEKLY => 'Weekly',
            self::RENEWAL_BI_WEEKLY => 'Bi-weekly',
            self::RENEWAL_MONTHLY => 'Monthly',
        ];
    }

    public static function getClassTypes(): array
    {
        return [
            self::CLASS_TYPE_GROUP => 'Group Class',
            self::CLASS_TYPE_WORKSHOP => 'Workshop',
            self::CLASS_TYPE_SPECIAL_EVENT => 'Special Event',
            self::CLASS_TYPE_MASTERCLASS => 'Masterclass',
            self::CLASS_TYPE_PRIVATE => 'Private Session',
        ];
    }

    public static function getValidityPresets(): array
    {
        return [
            ['type' => self::VALIDITY_DAYS, 'value' => 7, 'label' => '7 days'],
            ['type' => self::VALIDITY_DAYS, 'value' => 30, 'label' => '30 days'],
            ['type' => self::VALIDITY_DAYS, 'value' => 60, 'label' => '60 days'],
            ['type' => self::VALIDITY_DAYS, 'value' => 90, 'label' => '90 days'],
            ['type' => self::VALIDITY_MONTHS, 'value' => 6, 'label' => '6 months'],
            ['type' => self::VALIDITY_MONTHS, 'value' => 12, 'label' => '1 year'],
            ['type' => self::VALIDITY_NO_EXPIRATION, 'value' => null, 'label' => 'No expiration'],
        ];
    }
}
