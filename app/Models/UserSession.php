<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Agent\Agent;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'browser',
        'browser_version',
        'platform',
        'device_type',
        'location',
        'logged_in_at',
        'last_activity_at',
        'logged_out_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if ($session->user_agent && !$session->browser) {
                $agent = new Agent();
                $agent->setUserAgent($session->user_agent);
                $session->browser = $agent->browser() ?: 'Unknown';
                $session->browser_version = $agent->version($agent->browser()) ?: null;
                $session->platform = $agent->platform() ?: 'Unknown';
                $session->device_type = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
            }
        });
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors
     */
    public function getSessionDurationAttribute(): ?string
    {
        if (!$this->logged_in_at) {
            return null;
        }

        $end = $this->logged_out_at ?? ($this->is_active ? now() : $this->last_activity_at);

        if (!$end) {
            return null;
        }

        $diff = $this->logged_in_at->diff($end);

        if ($diff->days > 0) {
            return $diff->format('%d days %h hrs');
        } elseif ($diff->h > 0) {
            return $diff->format('%h hrs %i min');
        } else {
            return $diff->format('%i min');
        }
    }

    public function getDeviceIconAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => 'device-mobile',
            'tablet' => 'device-tablet',
            default => 'device-desktop',
        };
    }

    public function getBrowserIconAttribute(): string
    {
        $browser = strtolower($this->browser ?? '');

        return match(true) {
            str_contains($browser, 'chrome') => 'brand-chrome',
            str_contains($browser, 'firefox') => 'brand-firefox',
            str_contains($browser, 'safari') => 'brand-safari',
            str_contains($browser, 'edge') => 'brand-edge',
            str_contains($browser, 'opera') => 'brand-opera',
            default => 'world',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_active && $this->last_activity_at && $this->last_activity_at->diffInMinutes(now()) < 5) {
            return 'success'; // Online
        } elseif ($this->is_active) {
            return 'warning'; // Idle
        } else {
            return 'neutral'; // Offline
        }
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_active && $this->last_activity_at && $this->last_activity_at->diffInMinutes(now()) < 5) {
            return 'Online';
        } elseif ($this->is_active) {
            return 'Idle';
        } else {
            return 'Offline';
        }
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForHost(Builder $query, int $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 90): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get count of concurrent active sessions for a user
     */
    public static function getConcurrentSessionsCount(int $userId): int
    {
        return static::where('user_id', $userId)->active()->count();
    }

    /**
     * End the session
     */
    public function endSession(): void
    {
        $this->update([
            'logged_out_at' => now(),
            'is_active' => false,
        ]);
    }

    /**
     * Update last activity timestamp
     */
    public function recordActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }
}
