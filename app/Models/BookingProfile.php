<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingProfile extends Model
{
    use HasFactory;

    // Meeting type constants
    const MEETING_TYPE_IN_PERSON = 'in_person';
    const MEETING_TYPE_PHONE = 'phone';
    const MEETING_TYPE_VIDEO = 'video';

    /**
     * Default attribute values
     */
    protected $attributes = [
        'meeting_types' => '["in_person"]',
        'allowed_durations' => '[30, 60]',
        'working_days' => '[1,2,3,4,5]',
    ];

    protected $fillable = [
        'host_id',
        'instructor_id',
        'is_enabled',
        'is_setup_complete',
        'display_name',
        'title',
        'bio',
        'meeting_types',
        'video_link',
        'phone_number',
        'in_person_location',
        'allowed_durations',
        'default_duration',
        'buffer_before',
        'buffer_after',
        'daily_max_meetings',
        'min_notice_hours',
        'max_advance_days',
        'working_days',
        'availability_by_day',
        'default_start_time',
        'default_end_time',
        'allow_reschedule',
        'reschedule_cutoff_hours',
        'allow_cancel',
        'cancel_cutoff_hours',
        'invited_at',
        'setup_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_setup_complete' => 'boolean',
            'meeting_types' => 'array',
            'allowed_durations' => 'array',
            'working_days' => 'array',
            'availability_by_day' => 'array',
            'allow_reschedule' => 'boolean',
            'allow_cancel' => 'boolean',
            'invited_at' => 'datetime',
            'setup_completed_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(OneOnOneBooking::class);
    }

    /**
     * Scopes
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeSetupComplete($query)
    {
        return $query->where('is_setup_complete', true);
    }

    public function scopeForHost($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeAcceptingBookings($query)
    {
        return $query->where('is_enabled', true)->where('is_setup_complete', true);
    }

    /**
     * Check if profile can accept bookings
     */
    public function canAcceptBookings(): bool
    {
        return $this->is_enabled && $this->is_setup_complete;
    }

    /**
     * Get display name (fallback to instructor name)
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?? $this->instructor?->name ?? 'Unknown';
    }

    /**
     * Get title (fallback to empty)
     */
    public function getTitleDisplayAttribute(): ?string
    {
        return $this->title ?? $this->instructor?->specialties[0] ?? null;
    }

    /**
     * Get bio (fallback to instructor bio)
     */
    public function getBioDisplayAttribute(): ?string
    {
        return $this->bio ?? $this->instructor?->bio;
    }

    /**
     * Get photo URL (from instructor)
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->instructor?->photo_url;
    }

    /**
     * Get public URL for this booking profile
     */
    public function getPublicUrl(): string
    {
        return route('subdomain.instructor', [
            'subdomain' => $this->host->subdomain,
            'instructor' => $this->instructor_id,
        ]);
    }

    /**
     * Check if instructor works on a given day (0=Sunday, 6=Saturday)
     */
    public function worksOnDay(int $dayOfWeek): bool
    {
        if (empty($this->working_days)) {
            return true; // No restrictions means all days
        }
        return in_array($dayOfWeek, $this->working_days);
    }

    /**
     * Get availability window for a specific day
     * Returns ['from' => 'HH:MM', 'to' => 'HH:MM'] or null if no restrictions
     */
    public function getAvailabilityForDay(int $dayOfWeek): ?array
    {
        // Check day-specific override first
        if (!empty($this->availability_by_day)) {
            $dayKey = (string) $dayOfWeek;
            if (isset($this->availability_by_day[$dayKey]) &&
                !empty($this->availability_by_day[$dayKey]['from']) &&
                !empty($this->availability_by_day[$dayKey]['to'])) {
                return $this->availability_by_day[$dayKey];
            }
        }

        // Fall back to default availability
        if ($this->default_start_time && $this->default_end_time) {
            return [
                'from' => substr($this->default_start_time, 0, 5), // HH:MM format
                'to' => substr($this->default_end_time, 0, 5),
            ];
        }

        return null;
    }

    /**
     * Get available meeting types
     */
    public static function getMeetingTypes(): array
    {
        return [
            self::MEETING_TYPE_IN_PERSON => 'In Person',
            self::MEETING_TYPE_PHONE => 'Phone Call',
            self::MEETING_TYPE_VIDEO => 'Video Call',
        ];
    }

    /**
     * Get available duration options
     */
    public static function getDurationOptions(): array
    {
        return [
            15 => '15 minutes',
            30 => '30 minutes',
            45 => '45 minutes',
            60 => '1 hour',
        ];
    }

    /**
     * Get day of week options
     */
    public static function getDayOptions(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * Get formatted working days as comma-separated string
     */
    public function getFormattedWorkingDays(): string
    {
        if (empty($this->working_days)) {
            return 'All days';
        }

        $dayNames = self::getDayOptions();
        $days = array_map(fn($d) => $dayNames[$d] ?? '', $this->working_days);
        return implode(', ', array_filter($days));
    }
}
