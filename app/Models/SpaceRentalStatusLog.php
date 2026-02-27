<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceRentalStatusLog extends Model
{
    protected $fillable = [
        'space_rental_id',
        'from_status',
        'to_status',
        'notes',
        'updated_by',
    ];

    /**
     * Relationships
     */
    public function spaceRental(): BelongsTo
    {
        return $this->belongsTo(SpaceRental::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Display helpers
     */
    public function getFormattedFromStatusAttribute(): string
    {
        if (!$this->from_status) {
            return 'New';
        }
        return SpaceRental::getStatuses()[$this->from_status] ?? $this->from_status;
    }

    public function getFormattedToStatusAttribute(): string
    {
        return SpaceRental::getStatuses()[$this->to_status] ?? $this->to_status;
    }

    public function getStatusChangeLabelAttribute(): string
    {
        if (!$this->from_status) {
            return 'Created as ' . $this->formatted_to_status;
        }
        return $this->formatted_from_status . ' → ' . $this->formatted_to_status;
    }
}
