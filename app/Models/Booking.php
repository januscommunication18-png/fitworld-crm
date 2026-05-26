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
    const STATUS_WAITLISTED = 'waitlisted';
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

    // Manual payment sub-types (used when booked via public booking flow)
    const PAYMENT_VENMO = 'venmo';
    const PAYMENT_ZELLE = 'zelle';
    const PAYMENT_PAYPAL = 'paypal';
    const PAYMENT_CASH_APP = 'cash_app';
    const PAYMENT_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_CHECK = 'check';
    const PAYMENT_OTHER = 'other';

    // Booking source constants
    const SOURCE_ONLINE = 'online';
    const SOURCE_INTERNAL_WALKIN = 'internal_walkin';
    const SOURCE_API = 'api';

    // Intake status constants
    const INTAKE_NOT_REQUIRED = 'not_required';
    const INTAKE_PENDING = 'pending';
    const INTAKE_COMPLETED = 'completed';
    const INTAKE_WAIVED = 'waived';

    // Check-in method constants
    const CHECKIN_STAFF = 'staff';
    const CHECKIN_SELF = 'self';
    const CHECKIN_CARD_READER = 'card_reader';

    const TYPE_SINGLE = 'single';
    const TYPE_SERIES = 'series';

    protected $fillable = [
        'host_id',
        'client_id',
        'bookable_type',
        'bookable_id',
        'booking_type',
        'series_id',
        'status',
        'booking_source',
        'intake_status',
        'intake_waived_by',
        'intake_waived_reason',
        'capacity_override',
        'capacity_override_reason',
        'created_by_user_id',
        'payment_method',
        'billing_credit_id',
        'membership_id',
        'customer_membership_id',
        'class_pass_purchase_id',
        'price_paid',
        'credits_used',
        'booked_at',
        'cancelled_at',
        'cancellation_reason',
        'cancellation_notes',
        'cancelled_by_user_id',
        'is_late_cancellation',
        'checked_in_at',
        'checked_in_by_user_id',
        'checked_in_method',
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

    public function classPassPurchase(): BelongsTo
    {
        return $this->belongsTo(ClassPassPurchase::class);
    }

    public function billingCredit(): BelongsTo
    {
        return $this->belongsTo(BillingCredit::class);
    }

    /**
     * @deprecated Use classPassPurchase() instead
     */
    public function classPackPurchase(): BelongsTo
    {
        return $this->classPassPurchase();
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

    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
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
            self::STATUS_WAITLISTED => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-info',
            self::STATUS_CANCELLED => 'badge-neutral',
            self::STATUS_NO_SHOW => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Short human label for the payment method, e.g. 'Cash' / 'Card' / 'Bank
     * Transfer'. Drops verbose qualifiers ("Pay at Studio", "Card (Stripe)")
     * so it composes cleanly inside "Paid (X)".
     */
    public function getPaymentMethodShortLabelAttribute(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_STRIPE => 'Card',
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_MEMBERSHIP => 'Membership',
            self::PAYMENT_PACK => 'Class Pack',
            self::PAYMENT_COMP => 'Comp',
            self::PAYMENT_MANUAL => 'Manual',
            self::PAYMENT_VENMO => 'Venmo',
            self::PAYMENT_ZELLE => 'Zelle',
            self::PAYMENT_PAYPAL => 'PayPal',
            self::PAYMENT_CASH_APP => 'Cash App',
            self::PAYMENT_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_CHECK => 'Check',
            self::PAYMENT_OTHER => 'Other',
            default => (string) $this->payment_method,
        };
    }

    /**
     * Compose the payment cell label based on actual state:
     *   - Confirmed + price_paid > 0  → "Paid (Cash)" / "Paid (Card)" / ...
     *   - Confirmed + free (membership/pack/comp/0 paid) → method short label
     *   - Waitlisted / Pending payment → method label as-chosen (e.g. "Pay at Studio (Cash)")
     *   - Cancelled → method short label
     */
    public function getPaymentDisplayLabelAttribute(): string
    {
        $isFreeMethod = in_array($this->payment_method, [
            self::PAYMENT_MEMBERSHIP,
            self::PAYMENT_PACK,
            self::PAYMENT_COMP,
        ], true);

        if ($this->status === self::STATUS_CONFIRMED) {
            if (((float) $this->price_paid) > 0) {
                return 'Paid (' . $this->payment_method_short_label . ')';
            }
            if ($isFreeMethod) {
                return $this->payment_method_short_label;
            }
            // Confirmed with no recorded amount — treat as paid offline.
            return 'Paid (' . $this->payment_method_short_label . ')';
        }

        if ($this->status === self::STATUS_WAITLISTED) {
            return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return $this->payment_method_short_label;
        }

        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
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
            self::PAYMENT_COMP => 'badge-success',
            // Manual payment sub-types use info badge
            self::PAYMENT_VENMO,
            self::PAYMENT_ZELLE,
            self::PAYMENT_PAYPAL,
            self::PAYMENT_CASH_APP,
            self::PAYMENT_BANK_TRANSFER,
            self::PAYMENT_CHECK,
            self::PAYMENT_OTHER => 'badge-info',
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
     * Can the member self-check-in right now? Combines studio policy + the
     * configured check-in window around the session's start time.
     * Returns ['allowed' => bool, 'reason' => string|null] so callers can show
     * a helpful message ("Check-in opens in 12m", "Check-in window closed", etc.).
     */
    public function selfCheckInState(): array
    {
        $host = $this->host;
        if (!$host || !$host->getPolicy('allow_self_checkin', true)) {
            return ['allowed' => false, 'reason' => 'disabled'];
        }
        if ($this->status !== self::STATUS_CONFIRMED) {
            return ['allowed' => false, 'reason' => 'not_confirmed'];
        }
        if ($this->checked_in_at) {
            return ['allowed' => false, 'reason' => 'already'];
        }

        $bookable = $this->bookable;
        if (!$bookable || !$bookable->start_time) {
            return ['allowed' => false, 'reason' => 'no_session_time'];
        }

        $start = $bookable->start_time;
        $beforeMinutes = (int) $host->getPolicy('self_checkin_window_minutes', 30);
        $afterMinutes = (int) $host->getPolicy('self_checkin_late_minutes', 30);
        $opensAt = $start->copy()->subMinutes($beforeMinutes);
        $closesAt = $start->copy()->addMinutes($afterMinutes);

        if (now()->lt($opensAt)) {
            return ['allowed' => false, 'reason' => 'too_early', 'opens_at' => $opensAt];
        }
        if (now()->gt($closesAt)) {
            return ['allowed' => false, 'reason' => 'too_late', 'closed_at' => $closesAt];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function canSelfCheckIn(): bool
    {
        return $this->selfCheckInState()['allowed'];
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
     * Get relationship to user who checked in the booking
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_user_id')->withTrashed();
    }

    /**
     * Check in the booking
     */
    public function checkIn(?int $checkedInByUserId = null, string $method = self::CHECKIN_STAFF): bool
    {
        return $this->update([
            'checked_in_at' => now(),
            'checked_in_by_user_id' => $checkedInByUserId,
            'checked_in_method' => $method,
        ]);
    }

    /**
     * Get available check-in methods
     */
    public static function getCheckInMethods(): array
    {
        return [
            self::CHECKIN_STAFF => 'Staff',
            self::CHECKIN_SELF => 'Self Check-in',
            self::CHECKIN_CARD_READER => 'Card Reader',
        ];
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
            self::STATUS_WAITLISTED => 'Waitlisted',
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
            self::PAYMENT_CASH => 'Pay at Studio (Cash)',
            self::PAYMENT_COMP => 'Complimentary',
            // Manual payment sub-types from public booking flow
            self::PAYMENT_VENMO => 'Venmo',
            self::PAYMENT_ZELLE => 'Zelle',
            self::PAYMENT_PAYPAL => 'PayPal',
            self::PAYMENT_CASH_APP => 'Cash App',
            self::PAYMENT_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_CHECK => 'Check',
            self::PAYMENT_OTHER => 'Other',
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
