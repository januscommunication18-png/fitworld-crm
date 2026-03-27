<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\Host;
use App\Models\HostFeature;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeatureService
{
    /**
     * Check if a feature is available and enabled for a host
     */
    public function isEnabled(Host $host, string $featureSlug): bool
    {
        $feature = Feature::where('slug', $featureSlug)->where('is_active', true)->first();

        if (!$feature) {
            return false;
        }

        // Check if premium feature requires plan support
        if ($feature->isPremium() && !$this->hostCanAccessPremium($host, $feature)) {
            return false;
        }

        return HostFeature::where('host_id', $host->id)
            ->where('feature_id', $feature->id)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Check if host's plan supports premium features
     * Uses hybrid approach: plan-based OR backoffice-granted
     */
    public function hostCanAccessPremium(Host $host, Feature $feature): bool
    {
        // Check if host has manual premium access (backoffice granted)
        if ($host->has_premium_access) {
            return true;
        }

        // Check if host has active subscription or trial
        if (!in_array($host->subscription_status, [Host::SUBSCRIPTION_ACTIVE, Host::SUBSCRIPTION_TRIALING])) {
            return false;
        }

        // Check if plan allows premium features
        $plan = $host->plan;
        if (!$plan) {
            return false;
        }

        // Check if plan has premium_addons feature enabled
        return $plan->hasFeature('premium_addons');
    }

    /**
     * Enable a feature for a host
     */
    public function enableFeature(Host $host, Feature $feature, array $config = []): HostFeature
    {
        $hostFeature = HostFeature::firstOrCreate(
            ['host_id' => $host->id, 'feature_id' => $feature->id],
            ['config' => $feature->default_config ?? []]
        );

        $hostFeature->update([
            'is_enabled' => true,
            'activated_at' => now(),
            'deactivated_at' => null,
            'config' => array_merge($hostFeature->config ?? [], $config),
        ]);

        return $hostFeature;
    }

    /**
     * Disable a feature for a host
     */
    public function disableFeature(Host $host, Feature $feature): HostFeature
    {
        $hostFeature = HostFeature::where('host_id', $host->id)
            ->where('feature_id', $feature->id)
            ->firstOrFail();

        $hostFeature->update([
            'is_enabled' => false,
            'deactivated_at' => now(),
        ]);

        // Remove related permissions from team members
        $this->removeFeaturePermissions($host, $feature);

        return $hostFeature;
    }

    /**
     * Remove permissions related to a feature from all team members
     */
    protected function removeFeaturePermissions(Host $host, Feature $feature): void
    {
        // Map features to their related permissions
        $featurePermissions = [
            'price-override' => ['pricing.override'],
        ];

        $permissionsToRemove = $featurePermissions[$feature->slug] ?? [];

        if (empty($permissionsToRemove)) {
            return;
        }

        // Get all team members with custom permissions
        $teamMembers = $host->teamMembers()->get();

        foreach ($teamMembers as $user) {
            // Get permissions from pivot table
            $pivotPermissions = $user->pivot->permissions;
            if (is_string($pivotPermissions)) {
                $pivotPermissions = json_decode($pivotPermissions, true);
            }

            if (!empty($pivotPermissions) && is_array($pivotPermissions)) {
                $updatedPermissions = array_values(array_diff($pivotPermissions, $permissionsToRemove));

                // Update pivot table permissions
                DB::table('host_user')
                    ->where('host_id', $host->id)
                    ->where('user_id', $user->id)
                    ->update(['permissions' => json_encode($updatedPermissions)]);
            }

            // Also update user's direct permissions if set
            if (!empty($user->permissions) && is_array($user->permissions)) {
                $updatedUserPermissions = array_values(array_diff($user->permissions, $permissionsToRemove));
                $user->update(['permissions' => $updatedUserPermissions ?: null]);
            }
        }
    }

    /**
     * Get all features for marketplace display
     */
    public function getMarketplaceFeatures(Host $host): Collection
    {
        $features = Feature::active()->ordered()->get();
        $hostFeatures = HostFeature::getFeaturesForHost($host->id);

        return $features->map(function ($feature) use ($host, $hostFeatures) {
            $hostFeature = $hostFeatures->get($feature->id);

            return (object) [
                'feature' => $feature,
                'is_enabled' => $hostFeature?->is_enabled ?? false,
                'config' => $hostFeature?->config ?? $feature->default_config,
                'activated_at' => $hostFeature?->activated_at,
                'can_enable' => $feature->isFree() || $this->hostCanAccessPremium($host, $feature),
                'requires_upgrade' => $feature->isPremium() && !$this->hostCanAccessPremium($host, $feature),
            ];
        })->groupBy('feature.category');
    }

    /**
     * Get feature config for a host
     */
    public function getFeatureConfig(Host $host, string $featureSlug): ?array
    {
        $feature = Feature::where('slug', $featureSlug)->first();
        if (!$feature) {
            return null;
        }

        $hostFeature = HostFeature::getForHost($host->id, $feature->id);
        return $hostFeature?->config ?? $feature->default_config;
    }

    /**
     * Update feature config for a host
     */
    public function updateFeatureConfig(Host $host, Feature $feature, array $config): HostFeature
    {
        $hostFeature = HostFeature::where('host_id', $host->id)
            ->where('feature_id', $feature->id)
            ->firstOrFail();

        $hostFeature->update(['config' => $config]);

        return $hostFeature;
    }

    /**
     * Toggle a feature for a host
     */
    public function toggleFeature(Host $host, Feature $feature, bool $enable): HostFeature
    {
        if ($enable) {
            return $this->enableFeature($host, $feature);
        }

        return $this->disableFeature($host, $feature);
    }
}
