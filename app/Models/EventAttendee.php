<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAttendee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status Constants
     */
    const STATUS_REGISTERED = 'registered';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ATTENDED = 'attended';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_WAITLISTED = 'waitlisted';

    protected $fillable = [
        'event_id',
        'client_id',
        'added_by_user_id',
        'status',
        'registered_at',
        'confirmed_at',
        'checked_in_at',
        'checked_in_by_user_id',
        'cancelled_at',
        'cancellation_reason',
        'waitlist_position',
        'waitlist_joined_at',
        'waitlist_promoted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'waitlist_joined_at' => 'datetime',
            'waitlist_promoted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public static function getStatuses(): array
    {
        return [
            self::STATUS_REGISTERED => 'Registered',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_ATTENDED => 'Attended',
            self::STATUS_NO_SHOW => 'No Show',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_WAITLISTED => 'Waitlisted',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'info',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_ATTENDED => 'primary',
            self::STATUS_NO_SHOW => 'warning',
            self::STATUS_CANCELLED => 'error',
            self::STATUS_WAITLISTED => 'neutral',
            default => 'neutral',
        };
    }

    public function getIsRegisteredAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_REGISTERED, self::STATUS_CONFIRMED]);
    }

    public function getIsAttendedAttribute(): bool
    {
        return $this->status === self::STATUS_ATTENDED;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getCanCheckInAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_REGISTERED, self::STATUS_CONFIRMED]);
    }

    public function getCanCancelAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_REGISTERED, self::STATUS_CONFIRMED, self::STATUS_WAITLISTED]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeRegistered($query)
    {
        return $query->whereIn('status', [self::STATUS_REGISTERED, self::STATUS_CONFIRMED]);
    }

    public function scopeAttended($query)
    {
        return $query->where('status', self::STATUS_ATTENDED);
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('status', self::STATUS_WAITLISTED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function confirm(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function checkIn(int $userId = null): void
    {
        $this->update([
            'status' => self::STATUS_ATTENDED,
            'checked_in_at' => now(),
            'checked_in_by_user_id' => $userId,
        ]);

        // Update event registration count if coming from waitlist
        if ($this->getOriginal('status') === self::STATUS_WAITLISTED) {
            $this->event->increment('registration_count');
            $this->event->decrement('waitlist_count');
        }
    }

    public function markNoShow(): void
    {
        $this->update([
            'status' => self::STATUS_NO_SHOW,
        ]);
    }

    public function cancel(string $reason = null): void
    {
        $wasRegistered = in_array($this->status, [self::STATUS_REGISTERED, self::STATUS_CONFIRMED, self::STATUS_ATTENDED]);
        $wasWaitlisted = $this->status === self::STATUS_WAITLISTED;

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Update event counts
        if ($wasRegistered) {
            $this->event->decrement('registration_count');
        } elseif ($wasWaitlisted) {
            $this->event->decrement('waitlist_count');
        }
    }

    public function joinWaitlist(): void
    {
        $position = $this->event->attendees()->waitlisted()->max('waitlist_position') ?? 0;

        $this->update([
            'status' => self::STATUS_WAITLISTED,
            'waitlist_position' => $position + 1,
            'waitlist_joined_at' => now(),
        ]);

        $this->event->increment('waitlist_count');
    }

    public function promoteFromWaitlist(): void
    {
        $this->update([
            'status' => self::STATUS_REGISTERED,
            'waitlist_promoted_at' => now(),
            'registered_at' => now(),
            'waitlist_position' => null,
        ]);

        $this->event->increment('registration_count');
        $this->event->decrement('waitlist_count');
    }
}
