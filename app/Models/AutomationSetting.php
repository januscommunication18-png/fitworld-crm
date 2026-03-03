<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationSetting extends Model
{
    use HasFactory;

    const KEY_CLASS_REMINDER = 'class_reminder';
    const KEY_WELCOME_EMAIL = 'welcome_email';
    const KEY_WINBACK_CAMPAIGN = 'winback_campaign';

    protected $fillable = [
        'host_id',
        'key',
        'is_enabled',
        'config',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
            'last_run_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Get a setting by key for a specific host
     */
    public static function getForHost(int $hostId, string $key): ?self
    {
        return static::where('host_id', $hostId)->where('key', $key)->first();
    }

    /**
     * Check if an automation is enabled for a specific host
     */
    public static function isEnabledForHost(int $hostId, string $key): bool
    {
        $setting = static::getForHost($hostId, $key);
        return $setting?->is_enabled ?? false;
    }

    /**
     * Get all settings for a host
     */
    public static function getSettingsForHost(int $hostId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('host_id', $hostId)->get()->keyBy('key');
    }

    /**
     * Get all hosts with a specific automation enabled
     */
    public static function getEnabledHostIds(string $key): array
    {
        return static::where('key', $key)
            ->where('is_enabled', true)
            ->whereNotNull('host_id')
            ->pluck('host_id')
            ->toArray();
    }

    /**
     * Get config value with default
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set config value
     */
    public function setConfigValue(string $key, mixed $value): void
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
    }

    /**
     * Get all automation keys with labels
     */
    public static function getAutomationTypes(): array
    {
        return [
            self::KEY_CLASS_REMINDER => [
                'label' => 'Class Reminder',
                'description' => 'Send reminder emails to clients 24 hours before their scheduled class.',
                'icon' => 'bell',
                'default_config' => ['hours_before' => 24],
            ],
            self::KEY_WELCOME_EMAIL => [
                'label' => 'Welcome Email',
                'description' => 'Automatically send a welcome email when a new student signs up.',
                'icon' => 'mail-heart',
                'default_config' => ['delay_minutes' => 0],
            ],
            self::KEY_WINBACK_CAMPAIGN => [
                'label' => 'Win-back Campaign',
                'description' => 'Send emails to inactive members to encourage them to return.',
                'icon' => 'user-heart',
                'default_config' => ['days_inactive' => 60],
            ],
        ];
    }

    /**
     * Initialize default automation settings for a host
     */
    public static function initializeForHost(int $hostId): void
    {
        foreach (self::getAutomationTypes() as $key => $type) {
            static::firstOrCreate(
                ['host_id' => $hostId, 'key' => $key],
                [
                    'is_enabled' => false,
                    'config' => $type['default_config'],
                ]
            );
        }
    }

    /**
     * Get scheduled tasks info for backoffice
     */
    public static function getScheduledTasks(): array
    {
        return [
            [
                'command' => 'automation:class-reminders',
                'name' => 'Class Reminders',
                'description' => 'Sends reminder emails to clients with upcoming classes',
                'schedule' => 'Every Hour',
                'icon' => 'bell',
            ],
            [
                'command' => 'automation:winback',
                'name' => 'Win-back Campaign',
                'description' => 'Sends win-back emails to inactive clients',
                'schedule' => 'Daily at 9:00 AM',
                'icon' => 'user-heart',
            ],
            [
                'command' => 'audit:archive',
                'name' => 'Audit Log Archive',
                'description' => 'Archives audit logs older than 90 days to CSV',
                'schedule' => 'Daily at 3:00 AM',
                'icon' => 'archive',
            ],
            [
                'command' => 'clients:detect-at-risk',
                'name' => 'At-Risk Detection',
                'description' => 'Detects clients who may be at risk of churning',
                'schedule' => 'Daily at 2:00 AM',
                'icon' => 'alert-triangle',
            ],
        ];
    }
}
