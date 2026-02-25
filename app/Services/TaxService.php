<?php

namespace App\Services;

use App\DTOs\TaxCalculation;
use App\Models\Host;
use App\Models\Location;
use App\Models\TaxRate;
use Illuminate\Support\Collection;

class TaxService
{
    /**
     * Calculate tax for a transaction
     *
     * @param Host $host The studio host
     * @param float $subtotal The amount before tax
     * @param string $serviceType One of: class, service, membership, pack
     * @param Location|null $location Optional location for state/city lookup
     * @param mixed $plan Optional plan object to check for tax exemption
     * @return TaxCalculation
     */
    public function calculateTax(
        Host $host,
        float $subtotal,
        string $serviceType = 'class',
        ?Location $location = null,
        $plan = null
    ): TaxCalculation {
        // Check if tax is enabled for this host
        if (!$this->isTaxEnabled($host)) {
            return TaxCalculation::noTax($subtotal);
        }

        // Check if the plan is tax exempt
        if ($plan && $this->isExempt($host, $plan)) {
            return TaxCalculation::noTax($subtotal);
        }

        // Get location info
        $countryCode = $location?->country ?? $host->country ?? null;
        $stateCode = $location?->state ?? null;
        $city = $location?->city ?? null;

        if (!$countryCode) {
            return TaxCalculation::noTax($subtotal);
        }

        // Get applicable tax rates
        $rates = $this->getApplicableTaxRates($host, $countryCode, $stateCode, $city, $serviceType);

        if ($rates->isEmpty()) {
            return TaxCalculation::noTax($subtotal);
        }

        // Calculate tax components
        $components = [];
        $totalTax = 0;
        $taxBase = $subtotal;

        // Sort by priority and apply taxes
        $sortedRates = $rates->sortBy('priority');

        foreach ($sortedRates as $rate) {
            // For compound taxes, apply on top of previous taxes
            if ($rate->is_compound) {
                $taxBase = $subtotal + $totalTax;
            } else {
                $taxBase = $subtotal;
            }

            $taxAmount = $rate->calculateTax($taxBase);
            $totalTax += $taxAmount;

            $components[] = [
                'name' => $rate->tax_name,
                'type' => $rate->tax_type,
                'rate' => (float) $rate->rate,
                'amount' => $taxAmount,
                'is_compound' => $rate->is_compound,
            ];
        }

        // Round total tax
        $totalTax = round($totalTax, 2);
        $total = round($subtotal + $totalTax, 2);

        // Determine display mode
        $displayMode = $this->getDisplayMode($host, $countryCode);

        return new TaxCalculation(
            subtotal: $subtotal,
            totalTax: $totalTax,
            total: $total,
            components: $components,
            displayMode: $displayMode,
            countryCode: $countryCode
        );
    }

    /**
     * Get applicable tax rates for a location
     */
    public function getApplicableTaxRates(
        Host $host,
        ?string $countryCode = null,
        ?string $stateCode = null,
        ?string $city = null,
        ?string $serviceType = null
    ): Collection {
        if (!$countryCode) {
            return collect();
        }

        // Build query for applicable rates
        $query = TaxRate::query()
            ->forHost($host->id)
            ->forCountry($countryCode)
            ->active()
            ->effectiveOn();

        // Apply service type filter if specified
        if ($serviceType) {
            $query->appliesTo($serviceType);
        }

        $allRates = $query->orderBy('priority')->get();

        // Filter to most specific rates (state > country, city > state)
        return $this->filterToMostSpecific($allRates, $stateCode, $city);
    }

    /**
     * Filter rates to the most specific applicable ones
     * Prioritizes: host-specific > system default, city > state > country
     */
    protected function filterToMostSpecific(Collection $rates, ?string $stateCode, ?string $city): Collection
    {
        // Group by tax type to handle multiple tax types (e.g., GST + PST)
        $byType = $rates->groupBy('tax_type');

        $result = collect();

        foreach ($byType as $type => $typeRates) {
            // Prefer host-specific rates over system defaults
            $hostRates = $typeRates->whereNotNull('host_id');
            $systemRates = $typeRates->whereNull('host_id');

            $ratesPool = $hostRates->isNotEmpty() ? $hostRates : $systemRates;

            // Find most specific rate by location
            $selected = null;

            // 1. Try city-specific
            if ($city) {
                $selected = $ratesPool->firstWhere('city', $city);
            }

            // 2. Try state-specific
            if (!$selected && $stateCode) {
                $selected = $ratesPool->first(function ($rate) use ($stateCode) {
                    return $rate->state_code === $stateCode && !$rate->city;
                });
            }

            // 3. Fall back to country-level
            if (!$selected) {
                $selected = $ratesPool->first(function ($rate) {
                    return !$rate->state_code && !$rate->city;
                });
            }

            // If still no match, just take the first one for this state
            if (!$selected && $stateCode) {
                $selected = $ratesPool->firstWhere('state_code', $stateCode);
            }

            if ($selected) {
                $result->push($selected);
            }
        }

        return $result;
    }

    /**
     * Check if a plan is tax exempt
     */
    public function isExempt(Host $host, $plan): bool
    {
        // Check if plan has is_tax_exempt property
        if (property_exists($plan, 'is_tax_exempt') || isset($plan->is_tax_exempt)) {
            return (bool) $plan->is_tax_exempt;
        }

        // Check host's default tax exempt setting
        $taxSettings = $host->tax_settings ?? [];
        return $taxSettings['default_tax_exempt'] ?? false;
    }

    /**
     * Check if tax collection is enabled for a host
     */
    public function isTaxEnabled(Host $host): bool
    {
        $taxSettings = $host->tax_settings ?? [];
        return $taxSettings['tax_enabled'] ?? false;
    }

    /**
     * Get tax display mode for a country
     */
    protected function getDisplayMode(Host $host, string $countryCode): string
    {
        // Check host preference first
        $taxSettings = $host->tax_settings ?? [];
        $hostMode = $taxSettings['tax_display_mode'] ?? null;

        if ($hostMode) {
            return $hostMode;
        }

        // Default based on country
        return match ($countryCode) {
            'CA', 'IN' => 'itemized', // Canada and India typically show split taxes
            default => 'combined',
        };
    }

    /**
     * Get tax breakdown formatted for display
     */
    public function getTaxBreakdown(TaxCalculation $calculation, string $currencySymbol = '$'): array
    {
        if ($calculation->displayMode === 'combined' || count($calculation->components) <= 1) {
            // Single line display
            return [[
                'label' => 'Tax',
                'rate' => $calculation->getCombinedRate() . '%',
                'amount' => $currencySymbol . number_format($calculation->totalTax, 2),
            ]];
        }

        // Itemized display
        return array_map(function ($component) use ($currencySymbol) {
            return [
                'label' => $component['name'],
                'rate' => $component['rate'] . '%',
                'amount' => $currencySymbol . number_format($component['amount'], 2),
            ];
        }, $calculation->components);
    }

    /**
     * Get all tax rates for a host's operating countries
     */
    public function getTaxRatesForHost(Host $host): Collection
    {
        $operatingCountries = $host->operating_countries ?? [];

        if (empty($operatingCountries)) {
            // Default to host's primary country
            $operatingCountries = $host->country ? [$host->country] : [];
        }

        if (empty($operatingCountries)) {
            return collect();
        }

        return TaxRate::query()
            ->forHost($host->id)
            ->whereIn('country_code', $operatingCountries)
            ->orderBy('country_code')
            ->orderBy('state_code')
            ->orderBy('priority')
            ->get();
    }

    /**
     * Get system default rates for display (not host-specific)
     */
    public function getSystemRatesForCountries(array $countryCodes): Collection
    {
        return TaxRate::query()
            ->systemDefaults()
            ->whereIn('country_code', $countryCodes)
            ->active()
            ->orderBy('country_code')
            ->orderBy('state_code')
            ->orderBy('priority')
            ->get();
    }

    /**
     * Create a host-specific override of a system rate
     */
    public function createHostOverride(Host $host, TaxRate $systemRate, float $newRate): TaxRate
    {
        return TaxRate::create([
            'host_id' => $host->id,
            'country_code' => $systemRate->country_code,
            'state_code' => $systemRate->state_code,
            'city' => $systemRate->city,
            'tax_name' => $systemRate->tax_name,
            'tax_type' => $systemRate->tax_type,
            'rate' => $newRate,
            'is_compound' => $systemRate->is_compound,
            'priority' => $systemRate->priority,
            'applies_to' => $systemRate->applies_to,
            'is_active' => true,
        ]);
    }

    /**
     * Get default tax settings structure
     */
    public static function getDefaultTaxSettings(): array
    {
        return [
            'tax_enabled' => false,
            'tax_calculation_method' => 'exclusive', // exclusive or inclusive
            'tax_display_mode' => 'combined', // combined or itemized
            'tax_id' => null,
            'tax_id_label' => 'Tax ID',
            'show_tax_on_receipts' => true,
            'default_tax_exempt' => false,
            'exempt_payment_methods' => [], // e.g., ['membership', 'pack']
            'round_tax' => 'standard', // standard, up, down
        ];
    }
}
