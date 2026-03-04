<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostProgressTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'progress_template_id',
        'is_enabled',
        'custom_config',
        'activated_at',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'custom_config' => 'array',
            'activated_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    /**
     * The host this belongs to
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * The template this belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProgressTemplate::class, 'progress_template_id');
    }

    /**
     * Enable the template for the host
     */
    public function enable(): void
    {
        $this->update([
            'is_enabled' => true,
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);
    }

    /**
     * Disable the template for the host
     */
    public function disable(): void
    {
        $this->update([
            'is_enabled' => false,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Get or create pivot record for host + template
     */
    public static function getOrCreateForHost(int $hostId, int $templateId): self
    {
        return static::firstOrCreate(
            ['host_id' => $hostId, 'progress_template_id' => $templateId],
            ['is_enabled' => false]
        );
    }

    /**
     * Get enabled templates for a host
     */
    public static function getEnabledForHost(int $hostId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('host_id', $hostId)
            ->where('is_enabled', true)
            ->with('template')
            ->get();
    }

    /**
     * Check if template is enabled for host
     */
    public static function isEnabledForHost(int $hostId, int $templateId): bool
    {
        return static::where('host_id', $hostId)
            ->where('progress_template_id', $templateId)
            ->where('is_enabled', true)
            ->exists();
    }
}
