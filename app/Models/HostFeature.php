<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class HostFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'feature_id',
        'is_enabled',
        'config',
        'activated_at',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
            'activated_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * Get the host that owns this feature setting
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Get the feature definition
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Get a specific host feature record
     */
    public static function getForHost(int $hostId, int $featureId): ?self
    {
        return static::where('host_id', $hostId)
            ->where('feature_id', $featureId)
            ->first();
    }

    /**
     * Check if a feature is enabled for a host
     */
    public static function isEnabledForHost(int $hostId, int $featureId): bool
    {
        $setting = static::getForHost($hostId, $featureId);
        return $setting?->is_enabled ?? false;
    }

    /**
     * Get all enabled features for a host
     */
    public static function getEnabledFeaturesForHost(int $hostId): Collection
    {
        return static::where('host_id', $hostId)
            ->where('is_enabled', true)
            ->with('feature')
            ->get();
    }

    /**
     * Get all features for a host (keyed by feature_id)
     */
    public static function getFeaturesForHost(int $hostId): Collection
    {
        return static::where('host_id', $hostId)
            ->with('feature')
            ->get()
            ->keyBy('feature_id');
    }

    /**
     * Initialize all active features for a host (disabled by default)
     */
    public static function initializeForHost(int $hostId): void
    {
        $activeFeatures = Feature::active()->get();

        foreach ($activeFeatures as $feature) {
            static::firstOrCreate(
                ['host_id' => $hostId, 'feature_id' => $feature->id],
                [
                    'is_enabled' => false,
                    'config' => $feature->default_config,
                ]
            );
        }
    }

    /**
     * Get a config value
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set a config value
     */
    public function setConfigValue(string $key, mixed $value): void
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
    }
}
