<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ClassPlan extends Model
{
    use HasFactory;

    const CATEGORY_YOGA = 'yoga';
    const CATEGORY_PILATES = 'pilates';
    const CATEGORY_FITNESS = 'fitness';
    const CATEGORY_WELLNESS = 'wellness';
    const CATEGORY_OTHER = 'other';

    const TYPE_GROUP = 'group';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_SPECIAL_EVENT = 'special_event';

    const DIFFICULTY_BEGINNER = 'beginner';
    const DIFFICULTY_INTERMEDIATE = 'intermediate';
    const DIFFICULTY_ADVANCED = 'advanced';
    const DIFFICULTY_ALL_LEVELS = 'all_levels';

    protected $fillable = [
        'host_id',
        'name',
        'slug',
        'description',
        'category',
        'type',
        'default_duration_minutes',
        'default_capacity',
        'min_capacity',
        'default_price',
        'drop_in_price',
        'prices',
        'drop_in_prices',
        'new_member_prices',
        'new_member_drop_in_prices',
        'color',
        'difficulty_level',
        'equipment_needed',
        'image_path',
        'is_active',
        'is_visible_on_booking_page',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'equipment_needed' => 'array',
            'default_price' => 'decimal:2',
            'drop_in_price' => 'decimal:2',
            'prices' => 'array',
            'drop_in_prices' => 'array',
            'new_member_prices' => 'array',
            'new_member_drop_in_prices' => 'array',
            'is_active' => 'boolean',
            'is_visible_on_booking_page' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($classPlan) {
            if (empty($classPlan->slug)) {
                $classPlan->slug = Str::slug($classPlan->name);
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function scheduledClasses(): HasMany
    {
        return $this->hasMany(StudioClass::class, 'class_plan_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function classRequests(): HasMany
    {
        return $this->hasMany(ClassRequest::class);
    }

    public function questionnaireAttachments(): MorphMany
    {
        return $this->morphMany(QuestionnaireAttachment::class, 'attachable');
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return Storage::disk(config('filesystems.uploads'))->url($this->image_path);
        }
        return null;
    }

    /**
     * Get price for a specific currency
     */
    public function getPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        // Check prices JSON first
        if (!empty($this->prices) && isset($this->prices[$currency])) {
            return (float) $this->prices[$currency];
        }

        // Fall back to legacy default_price field
        return $this->default_price !== null ? (float) $this->default_price : null;
    }

    /**
     * Get drop-in price for a specific currency
     */
    public function getDropInPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        // Check drop_in_prices JSON first
        if (!empty($this->drop_in_prices) && isset($this->drop_in_prices[$currency])) {
            return (float) $this->drop_in_prices[$currency];
        }

        // Fall back to legacy drop_in_price field
        return $this->drop_in_price !== null ? (float) $this->drop_in_price : null;
    }

    /**
     * Get formatted price for a specific currency
     */
    public function getFormattedPriceForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $price = $this->getPriceForCurrency($currency);

        if ($price === null) {
            return 'Free';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    /**
     * Get formatted drop-in price for a specific currency
     */
    public function getFormattedDropInPriceForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $price = $this->getDropInPriceForCurrency($currency);

        if ($price === null) {
            return 'N/A';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    /**
     * Check if price is set for a currency
     */
    public function hasPriceForCurrency(string $currency): bool
    {
        return !empty($this->prices) && isset($this->prices[$currency]) && $this->prices[$currency] !== null;
    }

    /**
     * Get new member price for a specific currency
     */
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

    /**
     * Get new member drop-in price for a specific currency
     */
    public function getNewMemberDropInPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->new_member_drop_in_prices) && isset($this->new_member_drop_in_prices[$currency])) {
            return (float) $this->new_member_drop_in_prices[$currency];
        }

        return null;
    }

    /**
     * Get formatted new member price for a specific currency
     */
    public function getFormattedNewMemberPriceForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $price = $this->getNewMemberPriceForCurrency($currency);

        if ($price === null) {
            return 'Free';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    /**
     * Get formatted new member drop-in price for a specific currency
     */
    public function getFormattedNewMemberDropInPriceForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $price = $this->getNewMemberDropInPriceForCurrency($currency);

        if ($price === null) {
            return 'N/A';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($price, 2);
    }

    /**
     * Get formatted default price (uses default currency)
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->getFormattedPriceForCurrency();
    }

    /**
     * Get formatted drop-in price (uses default currency)
     */
    public function getFormattedDropInPriceAttribute(): string
    {
        return $this->getFormattedDropInPriceForCurrency();
    }

    /**
     * Get difficulty badge color
     */
    public function getDifficultyBadgeClass(): string
    {
        return match ($this->difficulty_level) {
            self::DIFFICULTY_BEGINNER => 'badge-success',
            self::DIFFICULTY_INTERMEDIATE => 'badge-warning',
            self::DIFFICULTY_ADVANCED => 'badge-error',
            default => 'badge-info',
        };
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->default_duration_minutes, 60);
        $minutes = $this->default_duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes} min";
    }

    /**
     * Scope active class plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope visible class plans
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->where('is_visible_on_booking_page', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_YOGA => 'Yoga',
            self::CATEGORY_PILATES => 'Pilates',
            self::CATEGORY_FITNESS => 'Fitness',
            self::CATEGORY_WELLNESS => 'Wellness',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    /**
     * Get available types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_GROUP => 'Group Class',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_SPECIAL_EVENT => 'Special Event',
        ];
    }

    /**
     * Get available difficulty levels
     */
    public static function getDifficultyLevels(): array
    {
        return [
            self::DIFFICULTY_BEGINNER => 'Beginner',
            self::DIFFICULTY_INTERMEDIATE => 'Intermediate',
            self::DIFFICULTY_ADVANCED => 'Advanced',
            self::DIFFICULTY_ALL_LEVELS => 'All Levels',
        ];
    }
}
