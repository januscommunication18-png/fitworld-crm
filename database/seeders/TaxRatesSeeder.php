<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds default tax rates for supported countries (host_id = null)
     */
    public function run(): void
    {
        // Clear existing system defaults
        TaxRate::whereNull('host_id')->delete();

        $rates = array_merge(
            $this->getUSARates(),
            $this->getCanadaRates(),
            $this->getMexicoRates(),
            $this->getGermanyRates(),
            $this->getFranceRates(),
            $this->getUKRates(),
            $this->getIndiaRates(),
            $this->getAustraliaRates()
        );

        foreach ($rates as $rate) {
            TaxRate::create(array_merge($rate, [
                'host_id' => null, // System default
                'is_active' => true,
            ]));
        }

        $this->command->info('Created ' . count($rates) . ' default tax rates.');
    }

    /**
     * USA State Sales Tax Rates (2025 base rates)
     * Note: These are state-level base rates. Cities/counties may add additional taxes.
     */
    protected function getUSARates(): array
    {
        return [
            // High population states
            ['country_code' => 'US', 'state_code' => 'CA', 'tax_name' => 'California Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 7.250, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'TX', 'tax_name' => 'Texas Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.250, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'FL', 'tax_name' => 'Florida Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NY', 'tax_name' => 'New York Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'PA', 'tax_name' => 'Pennsylvania Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'IL', 'tax_name' => 'Illinois Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.250, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'OH', 'tax_name' => 'Ohio Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.750, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'GA', 'tax_name' => 'Georgia Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NC', 'tax_name' => 'North Carolina Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.750, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MI', 'tax_name' => 'Michigan Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],

            // More states
            ['country_code' => 'US', 'state_code' => 'NJ', 'tax_name' => 'New Jersey Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.625, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'VA', 'tax_name' => 'Virginia Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.300, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'WA', 'tax_name' => 'Washington Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'AZ', 'tax_name' => 'Arizona Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.600, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MA', 'tax_name' => 'Massachusetts Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.250, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'TN', 'tax_name' => 'Tennessee Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 7.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'IN', 'tax_name' => 'Indiana Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 7.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MO', 'tax_name' => 'Missouri Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.225, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MD', 'tax_name' => 'Maryland Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'WI', 'tax_name' => 'Wisconsin Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'CO', 'tax_name' => 'Colorado Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 2.900, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MN', 'tax_name' => 'Minnesota Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.875, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'SC', 'tax_name' => 'South Carolina Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'AL', 'tax_name' => 'Alabama Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'LA', 'tax_name' => 'Louisiana Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.450, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'KY', 'tax_name' => 'Kentucky Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'OK', 'tax_name' => 'Oklahoma Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'CT', 'tax_name' => 'Connecticut Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.350, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'UT', 'tax_name' => 'Utah Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.100, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'IA', 'tax_name' => 'Iowa Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NV', 'tax_name' => 'Nevada Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.850, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'AR', 'tax_name' => 'Arkansas Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MS', 'tax_name' => 'Mississippi Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 7.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'KS', 'tax_name' => 'Kansas Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NM', 'tax_name' => 'New Mexico Gross Receipts Tax', 'tax_type' => 'sales_tax', 'rate' => 5.125, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NE', 'tax_name' => 'Nebraska Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'ID', 'tax_name' => 'Idaho Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'HI', 'tax_name' => 'Hawaii General Excise Tax', 'tax_type' => 'sales_tax', 'rate' => 4.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'WV', 'tax_name' => 'West Virginia Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'ME', 'tax_name' => 'Maine Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'RI', 'tax_name' => 'Rhode Island Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 7.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'SD', 'tax_name' => 'South Dakota Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.500, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'ND', 'tax_name' => 'North Dakota Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'VT', 'tax_name' => 'Vermont Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'WY', 'tax_name' => 'Wyoming Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 4.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'DC', 'tax_name' => 'District of Columbia Sales Tax', 'tax_type' => 'sales_tax', 'rate' => 6.000, 'priority' => 1],

            // No sales tax states (0%)
            ['country_code' => 'US', 'state_code' => 'OR', 'tax_name' => 'Oregon (No Sales Tax)', 'tax_type' => 'sales_tax', 'rate' => 0.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'MT', 'tax_name' => 'Montana (No Sales Tax)', 'tax_type' => 'sales_tax', 'rate' => 0.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'NH', 'tax_name' => 'New Hampshire (No Sales Tax)', 'tax_type' => 'sales_tax', 'rate' => 0.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'DE', 'tax_name' => 'Delaware (No Sales Tax)', 'tax_type' => 'sales_tax', 'rate' => 0.000, 'priority' => 1],
            ['country_code' => 'US', 'state_code' => 'AK', 'tax_name' => 'Alaska (No State Sales Tax)', 'tax_type' => 'sales_tax', 'rate' => 0.000, 'priority' => 1],
        ];
    }

    /**
     * Canada Provincial Tax Rates (2025)
     * Some provinces have HST (combined), others have GST + PST separately
     */
    protected function getCanadaRates(): array
    {
        return [
            // HST Provinces (single combined rate)
            ['country_code' => 'CA', 'state_code' => 'ON', 'tax_name' => 'Ontario HST', 'tax_type' => 'hst', 'rate' => 13.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'NB', 'tax_name' => 'New Brunswick HST', 'tax_type' => 'hst', 'rate' => 15.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'NL', 'tax_name' => 'Newfoundland HST', 'tax_type' => 'hst', 'rate' => 15.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'NS', 'tax_name' => 'Nova Scotia HST', 'tax_type' => 'hst', 'rate' => 15.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'PE', 'tax_name' => 'Prince Edward Island HST', 'tax_type' => 'hst', 'rate' => 15.000, 'priority' => 1],

            // GST + PST Provinces (separate rates - need both)
            // British Columbia
            ['country_code' => 'CA', 'state_code' => 'BC', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'BC', 'tax_name' => 'BC Provincial Sales Tax', 'tax_type' => 'pst', 'rate' => 7.000, 'priority' => 2],

            // Saskatchewan
            ['country_code' => 'CA', 'state_code' => 'SK', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'SK', 'tax_name' => 'Saskatchewan PST', 'tax_type' => 'pst', 'rate' => 6.000, 'priority' => 2],

            // Manitoba
            ['country_code' => 'CA', 'state_code' => 'MB', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'MB', 'tax_name' => 'Manitoba RST', 'tax_type' => 'pst', 'rate' => 7.000, 'priority' => 2],

            // Quebec (GST + QST)
            ['country_code' => 'CA', 'state_code' => 'QC', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'QC', 'tax_name' => 'Quebec Sales Tax (QST)', 'tax_type' => 'qst', 'rate' => 9.975, 'priority' => 2],

            // GST Only Provinces/Territories
            ['country_code' => 'CA', 'state_code' => 'AB', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'NT', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'NU', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
            ['country_code' => 'CA', 'state_code' => 'YT', 'tax_name' => 'Federal GST', 'tax_type' => 'gst', 'rate' => 5.000, 'priority' => 1],
        ];
    }

    /**
     * Mexico IVA Rates (2025)
     */
    protected function getMexicoRates(): array
    {
        return [
            ['country_code' => 'MX', 'state_code' => null, 'tax_name' => 'IVA (Standard)', 'tax_type' => 'iva', 'rate' => 16.000, 'priority' => 1],
            // Border region rate
            ['country_code' => 'MX', 'state_code' => 'BCN', 'tax_name' => 'IVA (Border Region)', 'tax_type' => 'iva', 'rate' => 8.000, 'priority' => 1],
            ['country_code' => 'MX', 'state_code' => 'SON', 'tax_name' => 'IVA (Border Region)', 'tax_type' => 'iva', 'rate' => 8.000, 'priority' => 1],
        ];
    }

    /**
     * Germany VAT Rates (Mehrwertsteuer) (2025)
     */
    protected function getGermanyRates(): array
    {
        return [
            ['country_code' => 'DE', 'state_code' => null, 'tax_name' => 'Mehrwertsteuer (Standard)', 'tax_type' => 'vat', 'rate' => 19.000, 'priority' => 1],
            ['country_code' => 'DE', 'state_code' => null, 'tax_name' => 'Mehrwertsteuer (Reduced)', 'tax_type' => 'vat', 'rate' => 7.000, 'priority' => 1, 'applies_to' => ['service']], // May apply to certain health services
        ];
    }

    /**
     * France TVA Rates (2025)
     */
    protected function getFranceRates(): array
    {
        return [
            ['country_code' => 'FR', 'state_code' => null, 'tax_name' => 'TVA (Standard)', 'tax_type' => 'vat', 'rate' => 20.000, 'priority' => 1],
            ['country_code' => 'FR', 'state_code' => null, 'tax_name' => 'TVA (Intermediate)', 'tax_type' => 'vat', 'rate' => 10.000, 'priority' => 1],
            ['country_code' => 'FR', 'state_code' => null, 'tax_name' => 'TVA (Reduced)', 'tax_type' => 'vat', 'rate' => 5.500, 'priority' => 1],
        ];
    }

    /**
     * United Kingdom VAT Rates (2025)
     */
    protected function getUKRates(): array
    {
        return [
            ['country_code' => 'GB', 'state_code' => null, 'tax_name' => 'VAT (Standard)', 'tax_type' => 'vat', 'rate' => 20.000, 'priority' => 1],
            ['country_code' => 'GB', 'state_code' => null, 'tax_name' => 'VAT (Reduced)', 'tax_type' => 'vat', 'rate' => 5.000, 'priority' => 1],
        ];
    }

    /**
     * India GST Rates (2025)
     * CGST + SGST for intra-state, IGST for inter-state
     */
    protected function getIndiaRates(): array
    {
        return [
            // Standard 18% GST for fitness services (9% CGST + 9% SGST)
            ['country_code' => 'IN', 'state_code' => null, 'tax_name' => 'Central GST (CGST)', 'tax_type' => 'cgst', 'rate' => 9.000, 'priority' => 1],
            ['country_code' => 'IN', 'state_code' => null, 'tax_name' => 'State GST (SGST)', 'tax_type' => 'sgst', 'rate' => 9.000, 'priority' => 2],
            // IGST for interstate (combined 18%)
            ['country_code' => 'IN', 'state_code' => null, 'tax_name' => 'Integrated GST (IGST)', 'tax_type' => 'igst', 'rate' => 18.000, 'priority' => 1],
        ];
    }

    /**
     * Australia GST Rate (2025)
     */
    protected function getAustraliaRates(): array
    {
        return [
            ['country_code' => 'AU', 'state_code' => null, 'tax_name' => 'Goods and Services Tax (GST)', 'tax_type' => 'gst', 'rate' => 10.000, 'priority' => 1],
        ];
    }
}
