<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    use HasFactory;

    // Tax types
    const TYPE_SALES_TAX = 'sales_tax';
    const TYPE_VAT = 'vat';
    const TYPE_GST = 'gst';
    const TYPE_PST = 'pst';
    const TYPE_HST = 'hst';
    const TYPE_QST = 'qst';
    const TYPE_CGST = 'cgst';
    const TYPE_SGST = 'sgst';
    const TYPE_IGST = 'igst';
    const TYPE_IVA = 'iva';

    protected $fillable = [
        'host_id',
        'country_code',
        'state_code',
        'city',
        'tax_name',
        'tax_type',
        'rate',
        'is_compound',
        'priority',
        'applies_to',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:3',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'applies_to' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    /**
     * System default rates (host_id is null)
     */
    public function scopeSystemDefaults($query)
    {
        return $query->whereNull('host_id');
    }

    /**
     * Custom rates for a specific host
     */
    public function scopeCustom($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Rates for a specific host (custom + system defaults)
     */
    public function scopeForHost($query, $hostId)
    {
        return $query->where(function ($q) use ($hostId) {
            $q->where('host_id', $hostId)
              ->orWhereNull('host_id');
        });
    }

    /**
     * Filter by country code
     */
    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Filter by state/province code
     */
    public function scopeForState($query, ?string $stateCode)
    {
        if ($stateCode === null) {
            return $query->whereNull('state_code');
        }
        return $query->where(function ($q) use ($stateCode) {
            $q->where('state_code', $stateCode)
              ->orWhereNull('state_code');
        });
    }

    /**
     * Filter by city
     */
    public function scopeForCity($query, ?string $city)
    {
        if ($city === null) {
            return $query->whereNull('city');
        }
        return $query->where(function ($q) use ($city) {
            $q->where('city', $city)
              ->orWhereNull('city');
        });
    }

    /**
     * Only active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Rates effective on a given date
     */
    public function scopeEffectiveOn($query, $date = null)
    {
        $date = $date ?? now()->toDateString();

        return $query->where(function ($q) use ($date) {
            $q->where(function ($inner) use ($date) {
                $inner->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', $date);
            })->where(function ($inner) use ($date) {
                $inner->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', $date);
            });
        });
    }

    /**
     * Rates that apply to a specific service type
     */
    public function scopeAppliesTo($query, string $serviceType)
    {
        return $query->where(function ($q) use ($serviceType) {
            $q->whereNull('applies_to')
              ->orWhereJsonContains('applies_to', $serviceType);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if this is a system default rate
     */
    public function isSystemDefault(): bool
    {
        return $this->host_id === null;
    }

    /**
     * Check if this rate applies to a specific service type
     */
    public function appliesToServiceType(string $serviceType): bool
    {
        // If applies_to is null, it applies to all
        if ($this->applies_to === null) {
            return true;
        }

        return in_array($serviceType, $this->applies_to);
    }

    /**
     * Check if rate is currently effective
     */
    public function isEffective(): bool
    {
        $now = now()->toDateString();

        if ($this->effective_from && $this->effective_from > $now) {
            return false;
        }

        if ($this->effective_to && $this->effective_to < $now) {
            return false;
        }

        return true;
    }

    /**
     * Calculate tax amount for a given subtotal
     */
    public function calculateTax(float $subtotal): float
    {
        return round($subtotal * ($this->rate / 100), 2);
    }

    /**
     * Get formatted rate (e.g., "8.25%")
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, $this->rate == floor($this->rate) ? 0 : 3) . '%';
    }

    /**
     * Get display name with location
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [$this->tax_name];

        if ($this->state_code) {
            $parts[] = "({$this->state_code})";
        }

        if ($this->city) {
            $parts[] = "- {$this->city}";
        }

        return implode(' ', $parts);
    }

    /**
     * Get country name from code
     */
    public function getCountryNameAttribute(): string
    {
        return self::getCountryNames()[$this->country_code] ?? $this->country_code;
    }

    /**
     * Get all supported country names
     */
    public static function getCountryNames(): array
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'MX' => 'Mexico',
            'DE' => 'Germany',
            'FR' => 'France',
            'GB' => 'United Kingdom',
            'IN' => 'India',
            'AU' => 'Australia',
        ];
    }

    /**
     * Get all tax type labels
     */
    public static function getTaxTypeLabels(): array
    {
        return [
            self::TYPE_SALES_TAX => 'Sales Tax',
            self::TYPE_VAT => 'VAT',
            self::TYPE_GST => 'GST',
            self::TYPE_PST => 'PST',
            self::TYPE_HST => 'HST',
            self::TYPE_QST => 'QST',
            self::TYPE_CGST => 'CGST',
            self::TYPE_SGST => 'SGST',
            self::TYPE_IGST => 'IGST',
            self::TYPE_IVA => 'IVA',
        ];
    }
}
