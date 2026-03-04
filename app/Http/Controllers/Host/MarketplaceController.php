<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\HostFeature;
use App\Services\FeatureService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function __construct(private FeatureService $featureService)
    {
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

        return view('host.marketplace.show', compact(
            'feature',
            'host',
            'hostFeature',
            'isEnabled',
            'config',
            'canEnable',
            'requiresUpgrade'
        ));
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
}
