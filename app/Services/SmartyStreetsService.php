<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SmartyStreetsService
{
    protected string $authId;
    protected string $authToken = '';
    protected string $websiteKey = '';
    protected string $baseUrl = 'https://us-street.api.smarty.com/street-address';
    protected string $autocompleteUrl = 'https://us-autocomplete-pro.api.smarty.com/lookup';

    public function __construct()
    {
        $this->authId = config('services.smarty.auth_id') ?? '';
        $this->authToken = config('services.smarty.auth_token') ?? '';
        $this->websiteKey = config('services.smarty.website_key') ?? '';
    }

    /**
     * Autocomplete address as user types (uses US Autocomplete Pro API)
     */
    public function autocomplete(string $search, ?string $stateFilter = null, int $maxResults = 10): array
    {
        if (empty($this->authId) || empty($this->authToken)) {
            return $this->fallbackAutocomplete($search, $stateFilter, $maxResults);
        }

        $cacheKey = 'smarty_autocomplete_' . md5($search . $stateFilter);

        return Cache::remember($cacheKey, 300, function () use ($search, $stateFilter, $maxResults) {
            try {
                $params = [
                    'auth-id' => $this->authId,
                    'auth-token' => $this->authToken,
                    'search' => $search,
                    'max_results' => $maxResults,
                    'prefer_geolocation' => 'none',
                ];

                if ($stateFilter) {
                    $params['include_only_states'] = $stateFilter;
                }

                $response = Http::get($this->autocompleteUrl, $params);

                if (!$response->successful()) {
                    \Log::error('SmartyStreets autocomplete API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }

                if ($response->successful()) {
                    $suggestions = $response->json('suggestions') ?? [];

                    if (!empty($suggestions)) {
                        return array_map(function ($suggestion) {
                            $street = $suggestion['street_line'] ?? '';
                            $secondary = $suggestion['secondary'] ?? '';
                            $city = $suggestion['city'] ?? '';
                            $state = $suggestion['state'] ?? '';
                            $zipcode = $suggestion['zipcode'] ?? '';
                            $entries = $suggestion['entries'] ?? 0;

                            $fullStreet = $secondary ? "{$street} {$secondary}" : $street;

                            return [
                                'street_line' => $fullStreet,
                                'city' => $city,
                                'state' => $state,
                                'state_name' => $state,
                                'zipcode' => $zipcode,
                                'entries' => $entries,
                                'label' => "{$fullStreet} {$city}, {$state} {$zipcode}",
                            ];
                        }, $suggestions);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('SmartyStreets autocomplete error: ' . $e->getMessage());
            }

            return $this->fallbackAutocomplete($search, $stateFilter, $maxResults);
        });
    }

    /**
     * Validate and standardize a full address
     */
    public function validateAddress(array $address): array
    {
        if (empty($this->authId) || empty($this->authToken)) {
            return $this->fallbackValidation($address);
        }

        try {
            $response = Http::get($this->baseUrl, [
                'auth-id' => $this->authId,
                'auth-token' => $this->authToken,
                'street' => $address['street'] ?? '',
                'city' => $address['city'] ?? '',
                'state' => $address['state'] ?? '',
                'zipcode' => $address['zipcode'] ?? '',
                'candidates' => 1,
            ]);

            if ($response->successful()) {
                $results = $response->json();

                if (!empty($results)) {
                    return $this->formatValidationResult($results[0]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('SmartyStreets validation error: ' . $e->getMessage());
        }

        return $this->fallbackValidation($address);
    }

    /**
     * Fallback to local database when API is not configured
     */
    protected function fallbackAutocomplete(string $search, ?string $stateFilter, int $maxResults): array
    {
        $query = \App\Models\UsCity::query();

        // Check if search looks like a zip code
        $isZipSearch = preg_match('/^\d+/', $search);

        $query->where(function ($q) use ($search, $isZipSearch) {
            $q->where('city', 'like', $search . '%')
              ->orWhere('city_ascii', 'like', $search . '%')
              ->orWhere('county_name', 'like', $search . '%');

            if ($isZipSearch) {
                $q->orWhere('zips', 'like', $search . '%')
                  ->orWhere('zips', 'like', '% ' . $search . '%');
            }
        });

        if ($stateFilter) {
            $query->where(function ($q) use ($stateFilter) {
                $q->where('state_id', $stateFilter)
                  ->orWhere('state_name', 'like', $stateFilter . '%');
            });
        }

        $cities = $query->orderBy('population', 'desc')
            ->limit($maxResults)
            ->get();

        return $cities->map(function ($city) use ($search) {
            // Find matching zip if searching by zip
            $matchedZip = null;
            if (preg_match('/^\d+/', $search) && $city->zips) {
                $zips = explode(' ', $city->zips);
                foreach ($zips as $zip) {
                    if (str_starts_with($zip, $search)) {
                        $matchedZip = $zip;
                        break;
                    }
                }
            }

            return [
                'street_line' => '',
                'city' => $city->city,
                'state' => $city->state_id,
                'state_name' => $city->state_name,
                'zipcode' => $matchedZip ?: ($city->zips ? explode(' ', $city->zips)[0] : ''),
                'county' => $city->county_name,
                'latitude' => $city->lat,
                'longitude' => $city->lng,
                'population' => $city->population,
                'timezone' => $city->timezone,
                'us_city_id' => $city->id,
                'label' => $matchedZip
                    ? "{$matchedZip} - {$city->city}, {$city->state_id}"
                    : "{$city->city}, {$city->state_id}",
            ];
        })->toArray();
    }

    /**
     * Fallback validation using local database
     */
    protected function fallbackValidation(array $address): array
    {
        $city = \App\Models\UsCity::where('city', $address['city'] ?? '')
            ->where(function ($q) use ($address) {
                $q->where('state_id', $address['state'] ?? '')
                  ->orWhere('state_name', $address['state'] ?? '');
            })
            ->first();

        if ($city) {
            $zipcode = $address['zipcode'] ?? '';
            if (empty($zipcode) && $city->zips) {
                $zipcode = explode(' ', $city->zips)[0];
            }

            return [
                'valid' => true,
                'street' => $address['street'] ?? '',
                'city' => $city->city,
                'state' => $city->state_id,
                'state_name' => $city->state_name,
                'zipcode' => $zipcode,
                'county' => $city->county_name,
                'latitude' => $city->lat,
                'longitude' => $city->lng,
                'us_city_id' => $city->id,
                'timezone' => $city->timezone,
            ];
        }

        return [
            'valid' => false,
            'street' => $address['street'] ?? '',
            'city' => $address['city'] ?? '',
            'state' => $address['state'] ?? '',
            'zipcode' => $address['zipcode'] ?? '',
            'error' => 'Could not validate address',
        ];
    }

    /**
     * Format SmartyStreets validation result
     */
    protected function formatValidationResult(array $result): array
    {
        $components = $result['components'] ?? [];
        $metadata = $result['metadata'] ?? [];

        return [
            'valid' => true,
            'street' => $result['delivery_line_1'] ?? '',
            'street2' => $result['delivery_line_2'] ?? '',
            'city' => $components['city_name'] ?? '',
            'state' => $components['state_abbreviation'] ?? '',
            'zipcode' => ($components['zipcode'] ?? '') . '-' . ($components['plus4_code'] ?? ''),
            'county' => $metadata['county_name'] ?? '',
            'latitude' => $metadata['latitude'] ?? null,
            'longitude' => $metadata['longitude'] ?? null,
            'timezone' => $metadata['time_zone'] ?? '',
            'delivery_point' => $metadata['delivery_point_barcode'] ?? '',
        ];
    }
}
