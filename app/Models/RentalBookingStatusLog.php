<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBookingStatusLog extends Model
{
    protected $fillable = [
        'rental_booking_id',
        'from_status',
        'to_status',
        'notes',
        'updated_by',
    ];

    public function rentalBooking(): BelongsTo
    {
        return $this->belongsTo(RentalBooking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFromStatusLabelAttribute(): string
    {
        if (!$this->from_status) {
            return 'New';
        }
        return RentalBooking::getStatuses()[$this->from_status] ?? $this->from_status;
    }

    public function getToStatusLabelAttribute(): string
    {
        return RentalBooking::getStatuses()[$this->to_status] ?? $this->to_status;
    }
}
