<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Host;
use App\Models\HostFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FitNearYouApiController extends Controller
{
    /**
     * Authenticate API request using API key and secret.
     */
    protected function authenticate(Request $request): ?Host
    {
        $apiKey = $request->header('X-FNY-Api-Key');
        $apiSecret = $request->header('X-FNY-Api-Secret');

        if (!$apiKey || !$apiSecret) {
            return null;
        }

        // Find the FitNearYou feature
        $feature = Feature::where('slug', 'fitnearyou-sync')->first();
        if (!$feature) {
            return null;
        }

        // Find host with matching API key
        $hostFeature = HostFeature::where('feature_id', $feature->id)
            ->where('is_enabled', true)
            ->whereRaw("JSON_EXTRACT(config, '$.api_key') = ?", [$apiKey])
            ->first();

        if (!$hostFeature) {
            return null;
        }

        // Verify the secret (stored as hash)
        $storedSecret = $hostFeature->config['api_secret'] ?? null;
        if (!$storedSecret || !hash_equals($storedSecret, hash('sha256', $apiSecret))) {
            return null;
        }

        return Host::find($hostFeature->host_id);
    }

    /**
     * Get studio info and available data for sync.
     */
    public function getStudioData(Request $request): JsonResponse
    {
        $host = $this->authenticate($request);

        if (!$host) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid API credentials',
            ], 401);
        }

        // Get sync settings
        $feature = Feature::where('slug', 'fitnearyou-sync')->first();
        $hostFeature = HostFeature::getForHost($host->id, $feature->id);
        $config = $hostFeature->config ?? [];

        $data = [
            'studio' => [
                'id' => $host->id,
                'name' => $host->studio_name,
                'subdomain' => $host->subdomain,
                'logo_url' => $host->logo_url,
                'description' => $host->about,
                'address' => $host->address,
                'city' => $host->city,
                'state' => $host->state,
                'country' => $host->country,
                'postal_code' => $host->postal_code,
                'phone' => $host->phone,
                'email' => $host->email,
                'website' => $host->website,
                'timezone' => $host->timezone,
            ],
            'sync_settings' => [
                'sync_classes' => $config['sync_classes'] ?? true,
                'sync_services' => $config['sync_services'] ?? true,
                'sync_deals' => $config['sync_deals'] ?? true,
                'sync_events' => $config['sync_events'] ?? true,
            ],
        ];

        // Include classes if enabled
        if ($config['sync_classes'] ?? true) {
            $data['classes'] = $this->getClasses($host);
        }

        // Include services if enabled
        if ($config['sync_services'] ?? true) {
            $data['services'] = $this->getServices($host);
        }

        // Include deals if enabled
        if ($config['sync_deals'] ?? true) {
            $data['deals'] = $this->getDeals($host);
        }

        // Include events if enabled
        if ($config['sync_events'] ?? true) {
            $data['events'] = $this->getEvents($host);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get classes data for sync.
     */
    protected function getClasses(Host $host): array
    {
        $classes = $host->classTypes()
            ->where('is_active', true)
            ->with(['location', 'instructors'])
            ->get();

        return $classes->map(function ($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'description' => $class->description,
                'duration_minutes' => $class->duration_minutes,
                'max_participants' => $class->max_participants,
                'price' => $class->price,
                'currency' => $class->currency ?? 'USD',
                'image_url' => $class->image_url,
                'category' => $class->category,
                'difficulty_level' => $class->difficulty_level,
                'location' => $class->location ? [
                    'name' => $class->location->name,
                    'address' => $class->location->address,
                ] : null,
                'instructors' => $class->instructors->map(fn($i) => [
                    'name' => $i->name,
                    'photo_url' => $i->photo_url,
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Get services data for sync.
     */
    protected function getServices(Host $host): array
    {
        $services = $host->services()
            ->where('is_active', true)
            ->with(['location', 'instructor'])
            ->get();

        return $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
                'currency' => $service->currency ?? 'USD',
                'image_url' => $service->image_url,
                'category' => $service->category,
                'location' => $service->location ? [
                    'name' => $service->location->name,
                    'address' => $service->location->address,
                ] : null,
                'instructor' => $service->instructor ? [
                    'name' => $service->instructor->name,
                    'photo_url' => $service->instructor->photo_url,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Get deals/offers data for sync.
     */
    protected function getDeals(Host $host): array
    {
        $offers = $host->offers()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        return $offers->map(function ($offer) {
            return [
                'id' => $offer->id,
                'name' => $offer->name,
                'description' => $offer->description,
                'discount_type' => $offer->discount_type,
                'discount_value' => $offer->discount_value,
                'code' => $offer->code,
                'starts_at' => $offer->starts_at?->toIso8601String(),
                'expires_at' => $offer->expires_at?->toIso8601String(),
                'image_url' => $offer->image_url,
            ];
        })->toArray();
    }

    /**
     * Get events data for sync.
     */
    protected function getEvents(Host $host): array
    {
        $events = $host->events()
            ->where('is_active', true)
            ->where('start_date', '>=', now())
            ->with(['location'])
            ->get();

        return $events->map(function ($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date?->toIso8601String(),
                'end_date' => $event->end_date?->toIso8601String(),
                'price' => $event->price,
                'currency' => $event->currency ?? 'USD',
                'max_participants' => $event->max_participants,
                'image_url' => $event->image_url,
                'location' => $event->location ? [
                    'name' => $event->location->name,
                    'address' => $event->location->address,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Verify API credentials (for testing connection).
     */
    public function verifyCredentials(Request $request): JsonResponse
    {
        $host = $this->authenticate($request);

        if (!$host) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid API credentials',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Credentials verified successfully',
            'studio' => [
                'id' => $host->id,
                'name' => $host->studio_name,
                'subdomain' => $host->subdomain,
            ],
        ]);
    }
}
