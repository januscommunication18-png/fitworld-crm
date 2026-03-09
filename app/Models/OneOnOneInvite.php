<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OneOnOneInvite extends Model
{
    protected $fillable = [
        'host_id',
        'instructor_id',
        'sent_by_user_id',
        'email',
        'client_name',
        'duration',
        'scheduled_at',
        'scheduled_slots',
        'sent_at',
        'opened_at',
        'booked_at',
        'booking_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'scheduled_slots' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'booked_at' => 'datetime',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(OneOnOneBooking::class, 'booking_id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->booked_at) {
            return 'booked';
        }
        if ($this->opened_at) {
            return 'opened';
        }
        return 'sent';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'booked' => 'badge-success',
            'opened' => 'badge-info',
            default => 'badge-neutral',
        };
    }

    /**
     * Check if the invite has specific time slots suggested.
     */
    public function hasScheduledSlots(): bool
    {
        return !empty($this->scheduled_slots) || $this->scheduled_at !== null;
    }

    /**
     * Get the total number of time slots across all dates.
     */
    public function getTotalSlotsCountAttribute(): int
    {
        if (empty($this->scheduled_slots)) {
            return $this->scheduled_at ? 1 : 0;
        }

        $count = 0;
        foreach ($this->scheduled_slots as $times) {
            $count += is_array($times) ? count($times) : 1;
        }
        return $count;
    }
}
