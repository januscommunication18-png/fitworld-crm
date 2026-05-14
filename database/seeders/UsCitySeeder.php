<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UsCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Download US cities CSV from SimpleMaps:
     * https://simplemaps.com/data/us-cities
     *
     * Place the CSV file at: database/data/uscities.csv
     */
    public function run(): void
    {
        $csvPath = database_path('data/uscities.csv');

        if (!File::exists($csvPath)) {
            $this->command->error('US Cities CSV file not found!');
            $this->command->info('Download from: https://simplemaps.com/data/us-cities');
            $this->command->info('Place at: database/data/uscities.csv');
            return;
        }

        $this->command->info('Importing US Cities...');

        // Truncate existing data
        DB::table('us_cities')->truncate();

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Get header row

        // Map CSV columns to database columns
        $columnMap = $this->getColumnMap($header);

        $batch = [];
        $batchSize = 1000;
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = $this->mapRowToData($row, $columnMap);

            if ($data) {
                $batch[] = $data;
                $count++;

                if (count($batch) >= $batchSize) {
                    DB::table('us_cities')->insert($batch);
                    $batch = [];
                    $this->command->info("Imported {$count} cities...");
                }
            }
        }

        // Insert remaining records
        if (!empty($batch)) {
            DB::table('us_cities')->insert($batch);
        }

        fclose($handle);

        $this->command->info("Successfully imported {$count} US cities!");
    }

    /**
     * Get column mapping from CSV header
     */
    private function getColumnMap(array $header): array
    {
        $map = [];
        foreach ($header as $index => $column) {
            $map[strtolower(trim($column))] = $index;
        }
        return $map;
    }

    /**
     * Map a CSV row to database data
     */
    private function mapRowToData(array $row, array $columnMap): ?array
    {
        $getValue = function($key) use ($row, $columnMap) {
            return isset($columnMap[$key]) && isset($row[$columnMap[$key]])
                ? trim($row[$columnMap[$key]])
                : null;
        };

        $city = $getValue('city');
        $stateId = $getValue('state_id');

        if (empty($city) || empty($stateId)) {
            return null;
        }

        return [
            'city' => $city,
            'city_ascii' => $getValue('city_ascii') ?? $city,
            'city_alt' => $getValue('city_alt'),
            'state_id' => $stateId,
            'state_name' => $getValue('state_name'),
            'county_fips' => $getValue('county_fips'),
            'county_name' => $getValue('county_name'),
            'county_fips_all' => $getValue('county_fips_all'),
            'county_name_all' => $getValue('county_name_all'),
            'lat' => $this->toDecimal($getValue('lat')),
            'lng' => $this->toDecimal($getValue('lng')),
            'population' => $this->toInt($getValue('population')),
            'population_proper' => $this->toInt($getValue('population_proper')),
            'density' => $this->toDecimal($getValue('density')),
            'source' => $getValue('source'),
            'military' => $this->toBool($getValue('military')),
            'incorporated' => $this->toBool($getValue('incorporated')),
            'cdp' => $this->toBool($getValue('cdp')),
            'timezone' => $getValue('timezone'),
            'ranking' => $this->toInt($getValue('ranking')),
            'zips' => $getValue('zips'),
            'simplemaps_id' => $getValue('id'),
            'age_median' => $this->toDecimal($getValue('age_median')),
            'male' => $this->toDecimal($getValue('male')),
            'female' => $this->toDecimal($getValue('female')),
            'married' => $this->toDecimal($getValue('married')),
            'family_size' => $this->toDecimal($getValue('family_size')),
            'income_household_median' => $this->toInt($getValue('income_household_median')),
            'income_household_six_figure' => $this->toDecimal($getValue('income_household_six_figure')),
            'home_ownership' => $this->toDecimal($getValue('home_ownership')),
            'home_value' => $this->toInt($getValue('home_value')),
            'rent_median' => $this->toInt($getValue('rent_median')),
            'education_college_or_above' => $this->toDecimal($getValue('education_college_or_above')),
            'labor_force_participation' => $this->toDecimal($getValue('labor_force_participation')),
            'unemployment_rate' => $this->toDecimal($getValue('unemployment_rate')),
            'race_white' => $this->toDecimal($getValue('race_white')),
            'race_black' => $this->toDecimal($getValue('race_black')),
            'race_asian' => $this->toDecimal($getValue('race_asian')),
            'race_native' => $this->toDecimal($getValue('race_native')),
            'race_pacific' => $this->toDecimal($getValue('race_pacific')),
            'race_other' => $this->toDecimal($getValue('race_other')),
            'race_multiple' => $this->toDecimal($getValue('race_multiple')),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function toDecimal($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function toInt($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function toBool($value): bool
    {
        return in_array(strtolower($value ?? ''), ['true', '1', 'yes'], true);
    }
}
