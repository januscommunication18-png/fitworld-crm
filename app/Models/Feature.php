<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    use HasFactory;

    const TYPE_FREE = 'free';
    const TYPE_PREMIUM = 'premium';

    const CATEGORY_TOOLS = 'tools';
    const CATEGORY_CALENDAR = 'calendar';
    const CATEGORY_PAYMENTS = 'payments';
    const CATEGORY_INTEGRATIONS = 'integrations';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'category',
        'is_active',
        'config_schema',
        'default_config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config_schema' => 'array',
            'default_config' => 'array',
        ];
    }

    /**
     * Hosts that have this feature
     */
    public function hosts(): BelongsToMany
    {
        return $this->belongsToMany(Host::class, 'host_features')
            ->withPivot(['is_enabled', 'config', 'activated_at', 'deactivated_at'])
            ->withTimestamps();
    }

    /**
     * Scope: only active features
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only free features
     */
    public function scopeFree($query)
    {
        return $query->where('type', self::TYPE_FREE);
    }

    /**
     * Scope: only premium features
     */
    public function scopePremium($query)
    {
        return $query->where('type', self::TYPE_PREMIUM);
    }

    /**
     * Scope: ordered by sort_order then name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if feature is free
     */
    public function isFree(): bool
    {
        return $this->type === self::TYPE_FREE;
    }

    /**
     * Check if feature is premium
     */
    public function isPremium(): bool
    {
        return $this->type === self::TYPE_PREMIUM;
    }

    /**
     * Get available feature types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_FREE => 'Free',
            self::TYPE_PREMIUM => 'Premium',
        ];
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_TOOLS => 'Tools & Features',
            self::CATEGORY_CALENDAR => 'Calendar Sync',
            self::CATEGORY_PAYMENTS => 'Payment Systems',
            self::CATEGORY_INTEGRATIONS => 'Integrations',
        ];
    }

    /**
     * Check if a feature is enabled for a specific host
     */
    public static function isEnabledForHost(int $hostId, string $slug): bool
    {
        return HostFeature::where('host_id', $hostId)
            ->whereHas('feature', fn($q) => $q->where('slug', $slug)->where('is_active', true))
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Get feature by slug for a host (with pivot data)
     */
    public static function getForHost(int $hostId, string $slug): ?self
    {
        return static::where('slug', $slug)
            ->where('is_active', true)
            ->with(['hosts' => fn($q) => $q->where('host_id', $hostId)])
            ->first();
    }
}
