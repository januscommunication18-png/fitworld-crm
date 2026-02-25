<?php

namespace App\DTOs;

class TaxCalculation
{
    public function __construct(
        public float $subtotal,
        public float $totalTax,
        public float $total,
        public array $components = [],
        public string $displayMode = 'combined',
        public string $countryCode = '',
    ) {}

    /**
     * Create from subtotal with no tax
     */
    public static function noTax(float $subtotal): self
    {
        return new self(
            subtotal: $subtotal,
            totalTax: 0,
            total: $subtotal,
            components: [],
            displayMode: 'combined'
        );
    }

    /**
     * Check if any tax was calculated
     */
    public function hasTax(): bool
    {
        return $this->totalTax > 0;
    }

    /**
     * Get combined tax rate as percentage
     */
    public function getCombinedRate(): float
    {
        if ($this->subtotal <= 0) {
            return 0;
        }

        return round(($this->totalTax / $this->subtotal) * 100, 3);
    }

    /**
     * Get formatted total tax
     */
    public function getFormattedTax(string $currencySymbol = '$'): string
    {
        return $currencySymbol . number_format($this->totalTax, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotal(string $currencySymbol = '$'): string
    {
        return $currencySymbol . number_format($this->total, 2);
    }

    /**
     * Convert to array for JSON storage
     */
    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'total_tax' => $this->totalTax,
            'total' => $this->total,
            'components' => $this->components,
            'display_mode' => $this->displayMode,
            'country_code' => $this->countryCode,
        ];
    }

    /**
     * Create from stored array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            subtotal: $data['subtotal'] ?? 0,
            totalTax: $data['total_tax'] ?? 0,
            total: $data['total'] ?? 0,
            components: $data['components'] ?? [],
            displayMode: $data['display_mode'] ?? 'combined',
            countryCode: $data['country_code'] ?? ''
        );
    }
}
