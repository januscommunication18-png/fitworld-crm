<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class RentalItem extends Model
{
    use HasFactory, SoftDeletes;

    // Category constants
    const CATEGORY_MAT = 'mat';
    const CATEGORY_TOWEL = 'towel';
    const CATEGORY_EQUIPMENT = 'equipment';
    const CATEGORY_APPAREL = 'apparel';
    const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'host_id',
        'name',
        'description',
        'sku',
        'category',
        'images',
        'prices',
        'deposit_amount',
        'deposit_prices',
        'total_inventory',
        'available_inventory',
        'requires_return',
        'max_rental_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'prices' => 'array',
            'deposit_prices' => 'array',
            'deposit_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'requires_return' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function eligibility(): HasMany
    {
        return $this->hasMany(RentalItemEligibility::class);
    }

    public function classPlans(): BelongsToMany
    {
        return $this->belongsToMany(ClassPlan::class, 'rental_item_class_plan')
            ->withPivot('is_required')
            ->withTimestamps();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(RentalInventoryLog::class);
    }

    /**
     * Multi-currency price methods
     */
    public function getPriceForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->prices) && isset($this->prices[$currency])) {
            return (float) $this->prices[$currency];
        }

        return null;
    }

    public function getDepositForCurrency(?string $currency = null): ?float
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        if (!empty($this->deposit_prices) && isset($this->deposit_prices[$currency])) {
            return (float) $this->deposit_prices[$currency];
        }

        return $this->deposit_amount !== null ? (float) $this->deposit_amount : null;
    }

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

    public function getFormattedDepositForCurrency(?string $currency = null): string
    {
        if ($currency === null) {
            $currency = $this->host?->default_currency ?? 'USD';
        }

        $deposit = $this->getDepositForCurrency($currency);

        if ($deposit === null || $deposit == 0) {
            return 'None';
        }

        $symbol = MembershipPlan::getCurrencySymbol($currency);
        return $symbol . number_format($deposit, 2);
    }

    public function hasPriceForCurrency(string $currency): bool
    {
        return !empty($this->prices) && isset($this->prices[$currency]) && $this->prices[$currency] !== null;
    }

    /**
     * Inventory methods
     */
    public function getAvailableForDate(Carbon $date): int
    {
        $bookedQuantity = $this->bookings()
            ->where('rental_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('due_date')
                  ->orWhere('due_date', '>=', $date);
            })
            ->whereNotIn('fulfillment_status', ['returned', 'lost'])
            ->sum('quantity');

        return max(0, $this->total_inventory - $bookedQuantity);
    }

    public function isAvailableForDateRange(Carbon $start, Carbon $end, int $quantity = 1): bool
    {
        // Check availability for each day in the range
        $current = $start->copy();
        while ($current <= $end) {
            if ($this->getAvailableForDate($current) < $quantity) {
                return false;
            }
            $current->addDay();
        }
        return true;
    }

    public function recalculateAvailableInventory(): int
    {
        $available = $this->getAvailableForDate(Carbon::today());
        $this->update(['available_inventory' => $available]);
        return $available;
    }

    public function isInStock(): bool
    {
        return $this->available_inventory > 0;
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->available_inventory > 0 && $this->available_inventory <= $threshold;
    }

    /**
     * Image helpers
     */
    public function getPrimaryImageAttribute(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        return $this->images[0] ?? null;
    }

    /**
     * Category helpers
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_MAT => 'Mat',
            self::CATEGORY_TOWEL => 'Towel',
            self::CATEGORY_EQUIPMENT => 'Equipment',
            self::CATEGORY_APPAREL => 'Apparel',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    public function getFormattedCategoryAttribute(): string
    {
        return self::getCategories()[$this->category] ?? $this->category ?? 'Uncategorized';
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_MAT => 'yoga',
            self::CATEGORY_TOWEL => 'ripple',
            self::CATEGORY_EQUIPMENT => 'barbell',
            self::CATEGORY_APPAREL => 'shirt',
            default => 'package',
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('available_inventory', '>', 0);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Eligibility checks
     */
    public function isAvailableToAll(): bool
    {
        return $this->eligibility()
            ->where('eligible_type', 'all')
            ->exists();
    }

    public function isAvailableForMembership(int $membershipPlanId): bool
    {
        if ($this->isAvailableToAll()) {
            return true;
        }

        return $this->eligibility()
            ->where('eligible_type', 'membership')
            ->where('membership_plan_id', $membershipPlanId)
            ->exists();
    }

    public function isFreeForMembership(int $membershipPlanId): bool
    {
        return $this->eligibility()
            ->where('eligible_type', 'membership')
            ->where('membership_plan_id', $membershipPlanId)
            ->where('is_free', true)
            ->exists();
    }

    public function getAvailableItems(Host $host, Carbon $date, ?string $category = null): Collection
    {
        $query = self::where('host_id', $host->id)
            ->active()
            ->inStock()
            ->ordered();

        if ($category) {
            $query->byCategory($category);
        }

        return $query->get()->filter(function ($item) use ($date) {
            return $item->getAvailableForDate($date) > 0;
        });
    }
}
