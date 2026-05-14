<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsCity;
use App\Services\SmartyStreetsService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected SmartyStreetsService $smarty;

    public function __construct(SmartyStreetsService $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * Autocomplete address/city/zip
     */
    public function autocomplete(Request $request)
    {
        $search = $request->get('q', '');
        $state = $request->get('state');
        $limit = min($request->get('limit', 10), 25);

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $results = $this->smarty->autocomplete($search, $state, $limit);

        return response()->json($results);
    }

    /**
     * Validate address
     */
    public function validate(Request $request)
    {
        $address = [
            'street' => $request->get('street', ''),
            'city' => $request->get('city', ''),
            'state' => $request->get('state', ''),
            'zipcode' => $request->get('zipcode', ''),
        ];

        $result = $this->smarty->validateAddress($address);

        return response()->json($result);
    }

    /**
     * Search cities with demographics
     */
    public function searchCities(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 15), 50);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $isZipSearch = preg_match('/^\d+/', $query);

        $cities = UsCity::query()
            ->where(function ($q) use ($query, $isZipSearch) {
                $q->where('city', 'like', $query . '%')
                  ->orWhere('city_ascii', 'like', $query . '%')
                  ->orWhere('county_name', 'like', $query . '%');

                if ($isZipSearch) {
                    $q->orWhere('zips', 'like', $query . '%')
                      ->orWhere('zips', 'like', '% ' . $query . '%');
                }
            })
            ->orderByRaw("CASE
                WHEN city LIKE ? THEN 0
                WHEN zips LIKE ? OR zips LIKE ? THEN 1
                ELSE 2
            END", [$query . '%', $query . '%', '% ' . $query . '%'])
            ->orderBy('population', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($cities->map(function ($city) use ($query) {
            $matchedZip = null;
            if (preg_match('/^\d+/', $query) && $city->zips) {
                $zips = explode(' ', $city->zips);
                foreach ($zips as $zip) {
                    if (str_starts_with($zip, $query)) {
                        $matchedZip = $zip;
                        break;
                    }
                }
            }

            return [
                'id' => $city->id,
                'city' => $city->city,
                'state_id' => $city->state_id,
                'state_name' => $city->state_name,
                'county' => $city->county_name,
                'zip' => $matchedZip ?: ($city->zips ? explode(' ', $city->zips)[0] : ''),
                'lat' => $city->lat,
                'lng' => $city->lng,
                'population' => $city->population,
                'timezone' => $city->timezone,
                'label' => $matchedZip
                    ? "{$matchedZip} - {$city->city}, {$city->state_id}"
                    : "{$city->city}, {$city->state_id}",
            ];
        }));
    }

    /**
     * Get states list
     */
    public function getStates()
    {
        $states = UsCity::select('state_id', 'state_name')
            ->distinct()
            ->orderBy('state_name')
            ->get()
            ->map(function ($state) {
                return [
                    'id' => $state->state_id,
                    'name' => $state->state_name,
                ];
            });

        return response()->json($states);
    }

    /**
     * Get zips for a city
     */
    public function getZips(Request $request)
    {
        $cityId = $request->get('city_id');
        $city = $request->get('city');
        $state = $request->get('state');

        $query = UsCity::query();

        if ($cityId) {
            $query->where('id', $cityId);
        } elseif ($city && $state) {
            $query->where('city', $city)
                  ->where(function ($q) use ($state) {
                      $q->where('state_id', $state)
                        ->orWhere('state_name', $state);
                  });
        } else {
            return response()->json([]);
        }

        $cityRecord = $query->first();

        if (!$cityRecord || !$cityRecord->zips) {
            return response()->json([]);
        }

        $zips = array_filter(explode(' ', $cityRecord->zips));

        return response()->json(array_map(function ($zip) use ($cityRecord) {
            return [
                'zip' => $zip,
                'city' => $cityRecord->city,
                'state' => $cityRecord->state_id,
                'lat' => $cityRecord->lat,
                'lng' => $cityRecord->lng,
            ];
        }, array_slice($zips, 0, 50)));
    }
}
