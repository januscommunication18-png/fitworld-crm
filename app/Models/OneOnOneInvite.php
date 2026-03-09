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
        'sent_at',
        'opened_at',
        'booked_at',
        'booking_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
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
}
