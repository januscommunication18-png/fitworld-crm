<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\HostFeature;
use App\Models\Instructor;
use App\Models\BookingProfile;
use App\Services\FeatureService;
use App\Services\BookingProfileInviteService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function __construct(
        private FeatureService $featureService,
        private BookingProfileInviteService $bookingProfileInviteService
    ) {
    }

    /**
     * Display the marketplace with all available features.
     */
    public function index()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $featuresGrouped = $this->featureService->getMarketplaceFeatures($host);
        $categories = Feature::getCategories();

        return view('host.marketplace.index', compact('featuresGrouped', 'categories', 'host'));
    }

    /**
     * Display a single feature with toggle and configuration options.
     */
    public function show(Feature $feature)
    {
        // Ensure feature is globally active
        if (!$feature->is_active) {
            abort(404);
        }

        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $hostFeature = HostFeature::getForHost($host->id, $feature->id);
        $isEnabled = $hostFeature?->is_enabled ?? false;
        $config = $hostFeature?->config ?? $feature->default_config ?? [];
        $canEnable = $feature->isFree() || $this->featureService->hostCanAccessPremium($host, $feature);
        $requiresUpgrade = $feature->isPremium() && !$this->featureService->hostCanAccessPremium($host, $feature);

        // Additional data for specific features
        $additionalData = [];

        if ($feature->slug === 'online-1on1-meeting') {
            $additionalData['instructors'] = $this->bookingProfileInviteService->getInstructorsWithStatus($host);
        }

        return view('host.marketplace.show', array_merge(compact(
            'feature',
            'host',
            'hostFeature',
            'isEnabled',
            'config',
            'canEnable',
            'requiresUpgrade'
        ), $additionalData));
    }

    /**
     * Toggle a feature on/off for the current host.
     */
    public function toggle(Request $request, Feature $feature)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;
        $enable = $request->boolean('enable');

        // Check if premium feature and host can access
        if ($feature->isPremium() && !$this->featureService->hostCanAccessPremium($host, $feature)) {
            return response()->json([
                'success' => false,
                'message' => 'Upgrade your plan to access premium features.',
                'requires_upgrade' => true,
            ], 403);
        }

        try {
            $this->featureService->toggleFeature($host, $feature, $enable);
            $message = $enable
                ? "{$feature->name} has been enabled."
                : "{$feature->name} has been disabled.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'is_enabled' => $enable,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Update feature configuration for the current host.
     */
    public function updateConfig(Request $request, Feature $feature)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $validated = $request->validate([
            'config' => 'required|array',
        ]);

        // Check if feature is enabled for this host
        $hostFeature = HostFeature::getForHost($host->id, $feature->id);
        if (!$hostFeature || !$hostFeature->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Feature must be enabled before configuring.',
            ], 400);
        }

        try {
            $this->featureService->updateFeatureConfig($host, $feature, $validated['config']);

            return response()->json([
                'success' => true,
                'message' => 'Configuration saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save configuration.',
            ], 500);
        }
    }

    /**
     * Grant 1:1 booking access to an instructor.
     */
    public function grantOneOnOneAccess(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructors,id',
        ]);

        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Check if 1:1 meeting feature is enabled
        if (!$host->hasFeature('online-1on1-meeting')) {
            return response()->json([
                'success' => false,
                'message' => 'Please enable the Online 1:1 Meeting feature first.',
            ], 400);
        }

        try {
            // Verify instructor belongs to this host
            $instructor = Instructor::where('host_id', $host->id)
                ->where('id', $validated['instructor_id'])
                ->first();

            if (!$instructor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instructor not found.',
                ], 404);
            }

            $profile = $this->bookingProfileInviteService->grantAccess($instructor);

            return response()->json([
                'success' => true,
                'message' => "1:1 booking access granted to {$instructor->name}.",
                'profile' => [
                    'id' => $profile->id,
                    'is_enabled' => $profile->is_enabled,
                    'is_setup_complete' => $profile->is_setup_complete,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grant access. Please try again.',
            ], 500);
        }
    }

    /**
     * Revoke 1:1 booking access from an instructor.
     */
    public function revokeOneOnOneAccess(Request $request, BookingProfile $bookingProfile)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify profile belongs to this host
        if ($bookingProfile->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Booking profile not found.',
            ], 404);
        }

        try {
            $instructorName = $bookingProfile->instructor?->name ?? 'Unknown';
            $this->bookingProfileInviteService->revokeAccess($bookingProfile);

            return response()->json([
                'success' => true,
                'message' => "1:1 booking access revoked for {$instructorName}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke access. Please try again.',
            ], 500);
        }
    }

    /**
     * Resend invitation email to an instructor.
     */
    public function resendOneOnOneInvitation(Request $request, BookingProfile $bookingProfile)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Verify profile belongs to this host
        if ($bookingProfile->host_id !== $host->id) {
            return response()->json([
                'success' => false,
                'message' => 'Booking profile not found.',
            ], 404);
        }

        try {
            $this->bookingProfileInviteService->resendInvitation($bookingProfile);
            $instructorName = $bookingProfile->instructor?->name ?? 'Unknown';

            return response()->json([
                'success' => true,
                'message' => "Invitation email resent to {$instructorName}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate API credentials for FitNearYou sync.
     */
    public function generateFitNearYouCredentials(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Get the FitNearYou feature
        $feature = Feature::where('slug', 'fitnearyou-sync')->first();

        if (!$feature || !$feature->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'FitNearYou sync feature is not available.',
            ], 404);
        }

        // Check if feature is enabled for this host
        $hostFeature = HostFeature::getForHost($host->id, $feature->id);

        if (!$hostFeature || !$hostFeature->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Please enable the FitNearYou Sync feature first.',
            ], 400);
        }

        try {
            // Generate new API credentials
            $apiKey = 'fny_' . Str::random(32);
            $apiSecret = Str::random(64);

            // Update config with new credentials
            $config = $hostFeature->config ?? [];
            $config['api_key'] = $apiKey;
            $config['api_secret'] = hash('sha256', $apiSecret); // Store hashed secret
            $config['credentials_generated_at'] = now()->toIso8601String();

            $hostFeature->update(['config' => $config]);

            return response()->json([
                'success' => true,
                'message' => 'API credentials generated successfully. Copy your secret key now - it will not be shown again.',
                'credentials' => [
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret, // Return plain text once
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate API credentials. Please try again.',
            ], 500);
        }
    }

    /**
     * Regenerate API credentials for FitNearYou sync.
     */
    public function regenerateFitNearYouCredentials(Request $request)
    {
        return $this->generateFitNearYouCredentials($request);
    }
}
