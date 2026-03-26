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
        $storedSecretHash = $hostFeature->config['api_secret_hash'] ?? $hostFeature->config['api_secret'] ?? null;
        if (!$storedSecretHash || !hash_equals($storedSecretHash, hash('sha256', $apiSecret))) {
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
                'sync_schedule' => $config['sync_schedule'] ?? true,
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

        // Include scheduled class sessions if enabled
        if ($config['sync_schedule'] ?? true) {
            $data['schedule'] = $this->getClassSessions($host);
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
        $classes = $host->classPlans()
            ->where('is_active', true)
            ->get();

        return $classes->map(function ($class) {
            $data = $class->toArray();
            // Add computed attributes
            $data['image_url'] = $class->image_url;
            $data['formatted_price'] = $class->formatted_price;
            $data['formatted_drop_in_price'] = $class->formatted_drop_in_price;
            $data['formatted_duration'] = $class->formatted_duration;
            return $data;
        })->toArray();
    }

    /**
     * Get services data for sync.
     */
    protected function getServices(Host $host): array
    {
        $services = $host->servicePlans()
            ->where('is_active', true)
            ->get();

        return $services->map(function ($service) {
            $data = $service->toArray();
            // Add computed attributes
            $data['image_url'] = $service->image_url;
            $data['formatted_price'] = $service->formatted_price;
            $data['formatted_duration'] = $service->formatted_duration;
            return $data;
        })->toArray();
    }

    /**
     * Get deals/offers data for sync.
     */
    protected function getDeals(Host $host): array
    {
        $offers = \App\Models\Offer::where('host_id', $host->id)
            ->where('status', \App\Models\Offer::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->get();

        return $offers->map(function ($offer) {
            $data = $offer->toArray();
            // Add computed attributes
            $data['formatted_discount'] = $offer->getFormattedDiscount();
            $data['is_available'] = $offer->isAvailable();
            return $data;
        })->toArray();
    }

    /**
     * Get events data for sync.
     */
    protected function getEvents(Host $host): array
    {
        $events = \App\Models\Event::where('host_id', $host->id)
            ->where('status', \App\Models\Event::STATUS_PUBLISHED)
            ->where('start_datetime', '>=', now())
            ->get();

        return $events->map(function ($event) {
            $data = $event->toArray();
            // Add computed attributes
            $data['full_address'] = $event->full_address;
            $data['formatted_date'] = $event->formatted_date;
            $data['formatted_time'] = $event->formatted_time;
            $data['spots_remaining'] = $event->spots_remaining;
            $data['is_sold_out'] = $event->is_sold_out;
            $data['event_type_label'] = $event->event_type_label;
            $data['skill_level_label'] = $event->skill_level_label;
            $data['status_label'] = $event->status_label;
            return $data;
        })->toArray();
    }

    /**
     * Get scheduled class sessions data for sync.
     * Include all sessions (not just future ones) so FitHQ can show history too.
     */
    protected function getClassSessions(Host $host): array
    {
        $sessions = \App\Models\ClassSession::where('host_id', $host->id)
            ->whereIn('status', [
                \App\Models\ClassSession::STATUS_PUBLISHED,
                \App\Models\ClassSession::STATUS_COMPLETED,
                \App\Models\ClassSession::STATUS_CANCELLED,
            ])
            ->with(['classPlan', 'primaryInstructor', 'location', 'room'])
            ->orderBy('start_time')
            ->get();

        return $sessions->map(function ($session) {
            $data = $session->toArray();
            // Add computed attributes
            $data['display_title'] = $session->display_title;
            $data['formatted_time_range'] = $session->formatted_time_range;
            $data['formatted_date'] = $session->formatted_date;
            $data['formatted_price'] = $session->formatted_price;
            $data['formatted_duration'] = $session->formatted_duration;
            $data['effective_price'] = $session->getEffectivePrice();
            $data['effective_capacity'] = $session->getEffectiveCapacity();
            $data['available_spots'] = $session->getAvailableSpots();
            $data['is_full'] = $session->isFull();
            // Include related data
            $data['class_plan_name'] = $session->classPlan?->name;
            $data['class_plan_category'] = $session->classPlan?->category;
            $data['class_plan_difficulty'] = $session->classPlan?->difficulty_level;
            $data['instructor_name'] = $session->primaryInstructor?->name;
            $data['location_name'] = $session->location?->name;
            $data['room_name'] = $session->room?->name;
            return $data;
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
