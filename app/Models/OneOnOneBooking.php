<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OneOnOneBooking extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_NO_SHOW = 'no_show';

    // Cancelled by constants
    const CANCELLED_BY_GUEST = 'guest';
    const CANCELLED_BY_HOST = 'host';

    protected $fillable = [
        'host_id',
        'booking_profile_id',
        'client_id',
        'guest_first_name',
        'guest_last_name',
        'guest_email',
        'guest_phone',
        'guest_notes',
        'meeting_type',
        'duration_minutes',
        'start_time',
        'end_time',
        'timezone',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'rescheduled_from_id',
        'reschedule_count',
        'confirmation_token',
        'manage_token',
        'booked_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'cancelled_at' => 'datetime',
            'booked_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Generate tokens if not set
            if (empty($booking->confirmation_token)) {
                $booking->confirmation_token = self::generateToken();
            }
            if (empty($booking->manage_token)) {
                $booking->manage_token = self::generateToken();
            }
            if (empty($booking->booked_at)) {
                $booking->booked_at = now();
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

    public function bookingProfile(): BelongsTo
    {
        return $this->belongsTo(BookingProfile::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(OneOnOneBooking::class, 'rescheduled_from_id');
    }

    /**
     * Scopes
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeNoShow($query)
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('start_time', '<', now());
    }

    public function scopeForProfile($query, $profileId)
    {
        return $query->where('booking_profile_id', $profileId);
    }

    public function scopeForHost($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }

    /**
     * Get guest full name
     */
    public function getGuestFullNameAttribute(): string
    {
        return trim($this->guest_first_name . ' ' . $this->guest_last_name);
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->start_time->format('l, M j, Y');
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '');
        }
        return "{$minutes} minutes";
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'badge-success',
            self::STATUS_COMPLETED => 'badge-info',
            self::STATUS_CANCELLED => 'badge-neutral',
            self::STATUS_NO_SHOW => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Get meeting type label
     */
    public function getMeetingTypeLabelAttribute(): string
    {
        return BookingProfile::getMeetingTypes()[$this->meeting_type] ?? ucfirst($this->meeting_type);
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if booking is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture() && $this->isConfirmed();
    }

    /**
     * Check if booking is in the past
     */
    public function isPast(): bool
    {
        return $this->start_time->isPast();
    }

    /**
     * Check if booking can be rescheduled
     */
    public function canBeRescheduled(): bool
    {
        if (!$this->isConfirmed() || $this->isPast()) {
            return false;
        }

        $profile = $this->bookingProfile;
        if (!$profile->allow_reschedule) {
            return false;
        }

        $cutoffHours = $profile->reschedule_cutoff_hours;
        $cutoffTime = $this->start_time->subHours($cutoffHours);

        return now()->isBefore($cutoffTime);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        if (!$this->isConfirmed() || $this->isPast()) {
            return false;
        }

        $profile = $this->bookingProfile;
        if (!$profile->allow_cancel) {
            return false;
        }

        $cutoffHours = $profile->cancel_cutoff_hours;
        $cutoffTime = $this->start_time->subHours($cutoffHours);

        return now()->isBefore($cutoffTime);
    }

    /**
     * Cancel the booking
     */
    public function cancel(string $cancelledBy, ?string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markComplete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    /**
     * Mark as no-show
     */
    public function markNoShow(): bool
    {
        return $this->update([
            'status' => self::STATUS_NO_SHOW,
        ]);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Find booking by manage token
     */
    public static function findByManageToken(string $token): ?self
    {
        return static::where('manage_token', $token)->first();
    }

    /**
     * Find booking by confirmation token
     */
    public static function findByConfirmationToken(string $token): ?self
    {
        return static::where('confirmation_token', $token)->first();
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_NO_SHOW => 'No Show',
        ];
    }
}
