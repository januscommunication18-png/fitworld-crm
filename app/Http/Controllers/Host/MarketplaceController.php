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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\FitNearYouVerificationCodeMail;

class MarketplaceController extends Controller
{
    public function __construct(
        private FeatureService $featureService,
        private BookingProfileInviteService $bookingProfileInviteService
    ) {
    }

    /**
     * Check if user has access to marketplace (owner or admin only)
     */
    private function authorizeMarketplaceAccess(): void
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        if (!$user->isOwner($host) && !$user->isAdmin($host)) {
            abort(403, 'Only owners and admins can access the Marketplace.');
        }
    }

    /**
     * Display the marketplace with all available features.
     */
    public function index()
    {
        $this->authorizeMarketplaceAccess();

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
        $this->authorizeMarketplaceAccess();

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
        $this->authorizeMarketplaceAccess();

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
            $config['api_secret_encrypted'] = Crypt::encryptString($apiSecret); // Store encrypted for retrieval
            $config['api_secret_hash'] = hash('sha256', $apiSecret); // Store hash for API authentication
            $config['credentials_generated_at'] = now()->toIso8601String();

            // Remove old api_secret key if exists
            unset($config['api_secret']);

            $hostFeature->update(['config' => $config]);

            return response()->json([
                'success' => true,
                'message' => 'API credentials generated successfully.',
                'credentials' => [
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
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

    /**
     * Send verification code to view API secret.
     */
    public function sendFitNearYouSecretCode(Request $request)
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Get the FitNearYou feature
        $feature = Feature::where('slug', 'fitnearyou-sync')->first();
        $hostFeature = HostFeature::getForHost($host->id, $feature->id);

        if (!$hostFeature || empty($hostFeature->config['api_secret_encrypted'])) {
            return response()->json([
                'success' => false,
                'message' => 'No API credentials found. Please generate credentials first.',
            ], 400);
        }

        // Generate 6-digit verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in cache for 10 minutes
        $cacheKey = "fitnearyou_secret_code_{$host->id}";
        Cache::put($cacheKey, [
            'code' => $code,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Send email with code
        try {
            Mail::to($user->email)->send(
                new FitNearYouVerificationCodeMail($code, $user->first_name ?? 'there')
            );

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify code and return API secret (visible for 2 minutes).
     */
    public function verifyFitNearYouSecretCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        $cacheKey = "fitnearyou_secret_code_{$host->id}";
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code expired. Please request a new code.',
            ], 400);
        }

        // Check attempts (max 3)
        if ($cached['attempts'] >= 3) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new code.',
            ], 400);
        }

        // Verify code
        if ($cached['code'] !== $request->code) {
            // Increment attempts
            Cache::put($cacheKey, [
                'code' => $cached['code'],
                'attempts' => $cached['attempts'] + 1,
            ], now()->addMinutes(10));

            $remaining = 3 - ($cached['attempts'] + 1);
            return response()->json([
                'success' => false,
                'message' => "Invalid code. {$remaining} attempts remaining.",
            ], 400);
        }

        // Code verified - get the secret
        $feature = Feature::where('slug', 'fitnearyou-sync')->first();
        $hostFeature = HostFeature::getForHost($host->id, $feature->id);

        if (!$hostFeature || empty($hostFeature->config['api_secret_encrypted'])) {
            return response()->json([
                'success' => false,
                'message' => 'No API credentials found.',
            ], 400);
        }

        try {
            $apiSecret = Crypt::decryptString($hostFeature->config['api_secret_encrypted']);

            // Clear the verification code
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Code verified. Secret visible for 2 minutes.',
                'api_secret' => $apiSecret,
                'expires_in' => 120, // 2 minutes in seconds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decrypt secret. Please regenerate credentials.',
            ], 500);
        }
    }
}
