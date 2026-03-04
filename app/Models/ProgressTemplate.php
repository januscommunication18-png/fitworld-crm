<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'studio_types',
        'scoring_model',
        'display_config',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'studio_types' => 'array',
            'scoring_model' => 'array',
            'display_config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Sections within this template
     */
    public function sections(): HasMany
    {
        return $this->hasMany(ProgressTemplateSection::class)->orderBy('sort_order');
    }

    /**
     * Hosts that have enabled this template
     */
    public function hosts(): BelongsToMany
    {
        return $this->belongsToMany(Host::class, 'host_progress_templates')
            ->withPivot(['is_enabled', 'custom_config', 'activated_at', 'deactivated_at'])
            ->withTimestamps();
    }

    /**
     * Client progress reports using this template
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ClientProgressReport::class);
    }

    /**
     * Get all metrics across all sections (flattened)
     */
    public function getAllMetrics()
    {
        return $this->sections()
            ->with('metrics')
            ->get()
            ->pluck('metrics')
            ->flatten();
    }

    /**
     * Scope: Active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope: Filter by studio type
     */
    public function scopeForStudioType($query, string $studioType)
    {
        return $query->whereJsonContains('studio_types', $studioType);
    }

    /**
     * Check if template is for a specific studio type
     */
    public function isForStudioType(string $studioType): bool
    {
        return in_array($studioType, $this->studio_types ?? []);
    }

    /**
     * Check if template is recommended for a host's studio types
     */
    public function isRecommendedForStudio(Host $host): bool
    {
        $hostStudioTypes = $host->studio_types ?? [];
        $templateStudioTypes = $this->studio_types ?? [];

        // Not recommended if template has no studio types defined
        if (empty($templateStudioTypes)) {
            return false;
        }

        // Recommended if there's any overlap between template and host studio types
        return !empty(array_intersect($templateStudioTypes, $hostStudioTypes));
    }

    /**
     * Check if host has enabled this template
     */
    public function isEnabledForHost(int $hostId): bool
    {
        return $this->hosts()
            ->where('host_id', $hostId)
            ->where('host_progress_templates.is_enabled', true)
            ->exists();
    }

    /**
     * Get templates available for a host (based on studio types)
     */
    public static function getAvailableForHost(Host $host): \Illuminate\Database\Eloquent\Collection
    {
        $studioTypes = $host->studio_types ?? [];

        return static::active()
            ->ordered()
            ->get()
            ->filter(function ($template) use ($studioTypes) {
                // Show templates that match any of the host's studio types
                // Or templates that are "universal" (empty studio_types)
                if (empty($template->studio_types)) {
                    return true;
                }
                return !empty(array_intersect($template->studio_types, $studioTypes));
            });
    }

    /**
     * Get enabled templates for a host
     */
    public static function getEnabledForHost(int $hostId): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->ordered()
            ->whereHas('hosts', function ($query) use ($hostId) {
                $query->where('host_id', $hostId)
                    ->where('host_progress_templates.is_enabled', true);
            })
            ->get();
    }
}
