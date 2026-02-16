<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Booking extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_COMPLETED = 'completed';

    // Payment method constants
    const PAYMENT_STRIPE = 'stripe';
    const PAYMENT_MEMBERSHIP = 'membership';
    const PAYMENT_PACK = 'pack';
    const PAYMENT_MANUAL = 'manual';
    const PAYMENT_CASH = 'cash';
    const PAYMENT_COMP = 'comp';

    // Booking source constants
    const SOURCE_ONLINE = 'online';
    const SOURCE_INTERNAL_WALKIN = 'internal_walkin';
    const SOURCE_API = 'api';

    // Intake status constants
    const INTAKE_NOT_REQUIRED = 'not_required';
    const INTAKE_PENDING = 'pending';
    const INTAKE_COMPLETED = 'completed';
    const INTAKE_WAIVED = 'waived';

    protected $fillable = [
        'host_id',
        'client_id',
        'bookable_type',
        'bookable_id',
        'status',
        'booking_source',
        'intake_status',
        'intake_waived_by',
        'intake_waived_reason',
        'capacity_override',
        'capacity_override_reason',
        'created_by_user_id',
        'payment_method',
        'membership_id',
        'customer_membership_id',
        'class_pack_purchase_id',
        'price_paid',
        'credits_used',
        'booked_at',
        'cancelled_at',
        'cancellation_reason',
        'cancellation_notes',
        'cancelled_by_user_id',
        'is_late_cancellation',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'capacity_override' => 'boolean',
            'is_late_cancellation' => 'boolean',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customerMembership(): BelongsTo
    {
        return $this->belongsTo(CustomerMembership::class);
    }

    public function classPackPurchase(): BelongsTo
    {
        return $this->belongsTo(ClassPackPurchase::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id')->withTrashed();
    }

    public function intakeWaivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intake_waived_by')->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get formatted price paid
     */
    public function getFormattedPricePaidAttribute(): string
    {
        if ($this->price_paid === null) {
            return 'Free';
        }
        return '$' . number_format($this->price_paid, 2);
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
     * Get payment method badge class
     */
    public function getPaymentMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_STRIPE => 'badge-primary',
            self::PAYMENT_MEMBERSHIP => 'badge-secondary',
            self::PAYMENT_PACK => 'badge-accent',
            self::PAYMENT_MANUAL => 'badge-info',
            self::PAYMENT_CASH => 'badge-warning',
            default => 'badge-neutral',
        };
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
     * Check if booking can be cancelled based on studio policy
     */
    public function canBeCancelled(): bool
    {
        // Already cancelled or completed
        if ($this->isCancelled() || $this->status === self::STATUS_COMPLETED) {
            return false;
        }

        // Check if host allows cancellations
        $host = $this->host;
        if (!$host->getPolicy('allow_cancellations', true)) {
            return false;
        }

        return true;
    }

    /**
     * Check if this would be a late cancellation
     */
    public function isLateCancellation(): bool
    {
        $bookable = $this->bookable;
        if (!$bookable || !$bookable->start_time) {
            return false;
        }

        $host = $this->host;
        $windowHours = $host->getPolicy('cancellation_window_hours', 12);

        // If window is 0, no cancellation is ever late
        if ($windowHours === 0) {
            return false;
        }

        $cutoffTime = $bookable->start_time->subHours($windowHours);
        return now()->isAfter($cutoffTime);
    }

    /**
     * Get the cancellation deadline
     */
    public function getCancellationDeadline(): ?\Carbon\Carbon
    {
        $bookable = $this->bookable;
        if (!$bookable || !$bookable->start_time) {
            return null;
        }

        $host = $this->host;
        $windowHours = $host->getPolicy('cancellation_window_hours', 12);

        if ($windowHours === 0) {
            return $bookable->start_time;
        }

        return $bookable->start_time->subHours($windowHours);
    }

    /**
     * Cancel the booking
     */
    public function cancel(?string $reason = null, ?string $notes = null, ?int $cancelledByUserId = null): bool
    {
        $isLate = $this->isLateCancellation();

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancellation_notes' => $notes,
            'cancelled_by_user_id' => $cancelledByUserId,
            'is_late_cancellation' => $isLate,
        ]);
    }

    /**
     * Get relationship to user who cancelled
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id')->withTrashed();
    }

    /**
     * Check if booking was no-show
     */
    public function isNoShow(): bool
    {
        return $this->status === self::STATUS_NO_SHOW;
    }

    /**
     * Check if booking is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if booking is checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Check if booking is a walk-in
     */
    public function isWalkIn(): bool
    {
        return $this->booking_source === self::SOURCE_INTERNAL_WALKIN;
    }

    /**
     * Check if booking is from online
     */
    public function isOnline(): bool
    {
        return $this->booking_source === self::SOURCE_ONLINE;
    }

    /**
     * Check if intake is required and pending
     */
    public function isIntakePending(): bool
    {
        return $this->intake_status === self::INTAKE_PENDING;
    }

    /**
     * Check if intake was waived
     */
    public function isIntakeWaived(): bool
    {
        return $this->intake_status === self::INTAKE_WAIVED;
    }

    /**
     * Check if capacity was overridden
     */
    public function hasCapacityOverride(): bool
    {
        return $this->capacity_override === true;
    }

    /**
     * Get booking source badge class
     */
    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->booking_source) {
            self::SOURCE_ONLINE => 'badge-primary',
            self::SOURCE_INTERNAL_WALKIN => 'badge-secondary',
            self::SOURCE_API => 'badge-accent',
            default => 'badge-neutral',
        };
    }

    /**
     * Get intake status badge class
     */
    public function getIntakeStatusBadgeClassAttribute(): string
    {
        return match ($this->intake_status) {
            self::INTAKE_COMPLETED => 'badge-success',
            self::INTAKE_PENDING => 'badge-warning',
            self::INTAKE_WAIVED => 'badge-info',
            self::INTAKE_NOT_REQUIRED => 'badge-neutral',
            default => 'badge-neutral',
        };
    }

    /**
     * Scope for host
     */
    public function scopeForHost($query, $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Scope for client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope no-show bookings
     */
    public function scopeNoShow($query)
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    /**
     * Scope active bookings (confirmed or completed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_COMPLETED]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_STRIPE => 'Card (Stripe)',
            self::PAYMENT_MEMBERSHIP => 'Membership',
            self::PAYMENT_PACK => 'Class Pack',
            self::PAYMENT_MANUAL => 'Manual',
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_COMP => 'Complimentary',
        ];
    }

    /**
     * Get available booking sources
     */
    public static function getBookingSources(): array
    {
        return [
            self::SOURCE_ONLINE => 'Online',
            self::SOURCE_INTERNAL_WALKIN => 'Walk-In',
            self::SOURCE_API => 'API',
        ];
    }

    /**
     * Get available intake statuses
     */
    public static function getIntakeStatuses(): array
    {
        return [
            self::INTAKE_NOT_REQUIRED => 'Not Required',
            self::INTAKE_PENDING => 'Pending',
            self::INTAKE_COMPLETED => 'Completed',
            self::INTAKE_WAIVED => 'Waived',
        ];
    }

    /**
     * Scope for walk-in bookings
     */
    public function scopeWalkIn($query)
    {
        return $query->where('booking_source', self::SOURCE_INTERNAL_WALKIN);
    }

    /**
     * Scope for online bookings
     */
    public function scopeOnline($query)
    {
        return $query->where('booking_source', self::SOURCE_ONLINE);
    }
}
