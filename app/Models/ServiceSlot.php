<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ServiceSlot extends Model
{
    use HasFactory;

    const STATUS_AVAILABLE = 'available';
    const STATUS_BOOKED = 'booked';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_DRAFT = 'draft';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'host_id',
        'service_plan_id',
        'instructor_id',
        'location_id',
        'room_id',
        'start_time',
        'end_time',
        'status',
        'price',
        'notes',
        'recurrence_rule',
        'recurrence_parent_id',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'cancelled_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(ServiceSlot::class, 'recurrence_parent_id');
    }

    public function recurrenceChildren(): HasMany
    {
        return $this->hasMany(ServiceSlot::class, 'recurrence_parent_id');
    }

    /**
     * Check if slot is available for booking
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE
            && $this->start_time->isFuture();
    }

    /**
     * Check if slot is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if slot is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Cancel the slot
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        return $this->save();
    }

    /**
     * Get effective price (slot override or service plan price)
     */
    public function getEffectivePrice(): ?float
    {
        if ($this->price !== null) {
            return (float) $this->price;
        }

        // Check instructor-specific price
        $instructorPrice = $this->servicePlan?->getPriceForInstructor($this->instructor);
        if ($instructorPrice !== null) {
            return $instructorPrice;
        }

        return $this->servicePlan?->price !== null ? (float) $this->servicePlan->price : null;
    }

    /**
     * Get formatted effective price
     */
    public function getFormattedPriceAttribute(): string
    {
        $price = $this->getEffectivePrice();
        if ($price === null) {
            return 'Free';
        }
        return '$' . number_format($price, 2);
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Mark slot as booked
     */
    public function markAsBooked(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->update(['status' => self::STATUS_BOOKED]);
        return true;
    }

    /**
     * Mark slot as available
     */
    public function markAsAvailable(): void
    {
        $this->update(['status' => self::STATUS_AVAILABLE]);
    }

    /**
     * Mark slot as blocked
     */
    public function markAsBlocked(): void
    {
        $this->update(['status' => self::STATUS_BLOCKED]);
    }

    /**
     * Scope available slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope upcoming slots
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope slots for a specific instructor
     */
    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Scope slots for a date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Scope slots for a specific date
     */
    public function scopeForDate($query, $date)
    {
        $date = Carbon::parse($date);
        return $query->whereDate('start_time', $date);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_BOOKED => 'Booked',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'badge-success',
            self::STATUS_BOOKED => 'badge-info',
            self::STATUS_BLOCKED => 'badge-neutral',
            self::STATUS_DRAFT => 'badge-warning',
            self::STATUS_CANCELLED => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Scope not cancelled slots
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Scope draft slots
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope cancelled slots
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
}
