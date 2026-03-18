<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Event Type Constants
     */
    const TYPE_IN_PERSON = 'in_person';
    const TYPE_ONLINE = 'online';
    const TYPE_HYBRID = 'hybrid';

    /**
     * Visibility Constants
     */
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_UNLISTED = 'unlisted';

    /**
     * Skill Level Constants
     */
    const SKILL_BEGINNER = 'beginner';
    const SKILL_INTERMEDIATE = 'intermediate';
    const SKILL_ADVANCED = 'advanced';
    const SKILL_ALL_LEVELS = 'all_levels';

    /**
     * Status Constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'host_id',
        'created_by_user_id',
        'title',
        'slug',
        'short_description',
        'description',
        'event_type',
        'visibility',
        'start_datetime',
        'end_datetime',
        'timezone',
        'venue_name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'online_url',
        'online_platform',
        'cover_image',
        'capacity',
        'registration_count',
        'waitlist_count',
        'skill_level',
        'audience_type',
        'waitlist_enabled',
        'hide_attendee_list',
        'status',
        'published_at',
        'cancelled_at',
        'cancellation_reason',
        'completed_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'published_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'waitlist_enabled' => 'boolean',
            'hide_attendee_list' => 'boolean',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title) . '-' . Str::random(6);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function registeredAttendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class)
            ->whereIn('status', ['registered', 'confirmed', 'attended']);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'event_attendees')
            ->withPivot(['status', 'registered_at', 'checked_in_at', 'notes'])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public static function getEventTypes(): array
    {
        return [
            self::TYPE_IN_PERSON => 'In Person',
            self::TYPE_ONLINE => 'Online',
            self::TYPE_HYBRID => 'Hybrid',
        ];
    }

    public function getEventTypeLabelAttribute(): string
    {
        return self::getEventTypes()[$this->event_type] ?? $this->event_type;
    }

    public static function getSkillLevels(): array
    {
        return [
            self::SKILL_ALL_LEVELS => 'All Levels',
            self::SKILL_BEGINNER => 'Beginner',
            self::SKILL_INTERMEDIATE => 'Intermediate',
            self::SKILL_ADVANCED => 'Advanced',
        ];
    }

    public function getSkillLevelLabelAttribute(): string
    {
        return self::getSkillLevels()[$this->skill_level] ?? $this->skill_level;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'warning',
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_CANCELLED => 'error',
            self::STATUS_COMPLETED => 'info',
            default => 'neutral',
        };
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->zip_code,
        ]);
        return implode(', ', $parts);
    }

    public function getFormattedDateAttribute(): string
    {
        if (!$this->start_datetime || !$this->end_datetime) {
            return '';
        }

        if ($this->start_datetime->isSameDay($this->end_datetime)) {
            return $this->start_datetime->format('l, F j, Y');
        }
        return $this->start_datetime->format('M j') . ' - ' . $this->end_datetime->format('M j, Y');
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->start_datetime || !$this->end_datetime) {
            return '';
        }
        return $this->start_datetime->format('g:i A') . ' - ' . $this->end_datetime->format('g:i A');
    }

    public function getSpotsRemainingAttribute(): ?int
    {
        if ($this->capacity === null) {
            return null; // Unlimited
        }
        return max(0, $this->capacity - $this->registration_count);
    }

    public function getIsSoldOutAttribute(): bool
    {
        if ($this->capacity === null) {
            return false;
        }
        return $this->registration_count >= $this->capacity;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getIsPastAttribute(): bool
    {
        return $this->end_datetime && $this->end_datetime->isPast();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_datetime && $this->start_datetime->isFuture();
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->start_datetime && $this->end_datetime
            && $this->start_datetime->isPast()
            && $this->end_datetime->isFuture();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForHost(Builder $query, int $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_datetime', '>', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_datetime', '<', now());
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('start_datetime', '<=', now())
                     ->where('end_datetime', '>=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function canAddAttendees(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        if ($this->is_past) {
            return false;
        }

        if ($this->capacity !== null && $this->registration_count >= $this->capacity) {
            return $this->waitlist_enabled;
        }

        return true;
    }

    public function isClientRegistered(int $clientId): bool
    {
        return $this->attendees()
            ->where('client_id', $clientId)
            ->whereNotIn('status', ['cancelled'])
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
