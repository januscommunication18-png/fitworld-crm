<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    use HasFactory;

    // Common action types
    const ACTION_BOOKING_CREATED = 'booking.created';
    const ACTION_BOOKING_CANCELLED = 'booking.cancelled';
    const ACTION_BOOKING_CHECKED_IN = 'booking.checked_in';
    const ACTION_BOOKING_CAPACITY_OVERRIDDEN = 'booking.capacity_overridden';
    const ACTION_BOOKING_INTAKE_WAIVED = 'booking.intake_waived';

    const ACTION_PAYMENT_PROCESSED = 'payment.processed';
    const ACTION_PAYMENT_REFUNDED = 'payment.refunded';
    const ACTION_PAYMENT_FAILED = 'payment.failed';

    const ACTION_MEMBERSHIP_CREATED = 'membership.created';
    const ACTION_MEMBERSHIP_PAUSED = 'membership.paused';
    const ACTION_MEMBERSHIP_RESUMED = 'membership.resumed';
    const ACTION_MEMBERSHIP_CANCELLED = 'membership.cancelled';
    const ACTION_MEMBERSHIP_RENEWED = 'membership.renewed';

    const ACTION_PACK_PURCHASED = 'pack.purchased';
    const ACTION_PACK_CREDIT_USED = 'pack.credit_used';
    const ACTION_PACK_CREDIT_RESTORED = 'pack.credit_restored';

    const ACTION_CLIENT_CREATED = 'client.created';
    const ACTION_CLIENT_UPDATED = 'client.updated';

    protected $fillable = [
        'host_id',
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'before_data',
        'after_data',
        'context',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
            'context' => 'array',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessors
     */
    public function getActionLabelAttribute(): string
    {
        $labels = [
            self::ACTION_BOOKING_CREATED => 'Booking Created',
            self::ACTION_BOOKING_CANCELLED => 'Booking Cancelled',
            self::ACTION_BOOKING_CHECKED_IN => 'Client Checked In',
            self::ACTION_BOOKING_CAPACITY_OVERRIDDEN => 'Capacity Override',
            self::ACTION_BOOKING_INTAKE_WAIVED => 'Intake Waived',
            self::ACTION_PAYMENT_PROCESSED => 'Payment Processed',
            self::ACTION_PAYMENT_REFUNDED => 'Payment Refunded',
            self::ACTION_PAYMENT_FAILED => 'Payment Failed',
            self::ACTION_MEMBERSHIP_CREATED => 'Membership Created',
            self::ACTION_MEMBERSHIP_PAUSED => 'Membership Paused',
            self::ACTION_MEMBERSHIP_RESUMED => 'Membership Resumed',
            self::ACTION_MEMBERSHIP_CANCELLED => 'Membership Cancelled',
            self::ACTION_MEMBERSHIP_RENEWED => 'Membership Renewed',
            self::ACTION_PACK_PURCHASED => 'Pack Purchased',
            self::ACTION_PACK_CREDIT_USED => 'Pack Credit Used',
            self::ACTION_PACK_CREDIT_RESTORED => 'Pack Credit Restored',
            self::ACTION_CLIENT_CREATED => 'Client Created',
            self::ACTION_CLIENT_UPDATED => 'Client Updated',
        ];

        return $labels[$this->action] ?? ucwords(str_replace(['.', '_'], ' ', $this->action));
    }

    public function getActionCategoryAttribute(): string
    {
        $parts = explode('.', $this->action);
        return $parts[0] ?? 'unknown';
    }

    public function getIsWarningAttribute(): bool
    {
        return in_array($this->action, [
            self::ACTION_BOOKING_CAPACITY_OVERRIDDEN,
            self::ACTION_BOOKING_INTAKE_WAIVED,
            self::ACTION_PAYMENT_FAILED,
        ]);
    }

    public function getIsErrorAttribute(): bool
    {
        return in_array($this->action, [
            self::ACTION_PAYMENT_FAILED,
        ]);
    }

    /**
     * Create an audit log entry
     */
    public static function log(
        int $hostId,
        string $action,
        ?Model $auditable = null,
        array $options = []
    ): self {
        $user = auth()->user();

        return self::create([
            'host_id' => $hostId,
            'user_id' => $user?->id ?? $options['user_id'] ?? null,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'before_data' => $options['before_data'] ?? null,
            'after_data' => $options['after_data'] ?? null,
            'context' => $options['context'] ?? null,
            'reason' => $options['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a booking capacity override
     */
    public static function logCapacityOverride(Booking $booking, string $reason): self
    {
        return self::log(
            $booking->host_id,
            self::ACTION_BOOKING_CAPACITY_OVERRIDDEN,
            $booking,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $booking->client_id,
                    'bookable_type' => $booking->bookable_type,
                    'bookable_id' => $booking->bookable_id,
                ],
            ]
        );
    }

    /**
     * Log an intake waiver
     */
    public static function logIntakeWaive(Booking $booking, string $reason): self
    {
        return self::log(
            $booking->host_id,
            self::ACTION_BOOKING_INTAKE_WAIVED,
            $booking,
            [
                'reason' => $reason,
                'context' => [
                    'client_id' => $booking->client_id,
                ],
            ]
        );
    }

    /**
     * Log a payment action
     */
    public static function logPaymentAction(Payment $payment, string $action): self
    {
        return self::log(
            $payment->host_id,
            $action,
            $payment,
            [
                'context' => [
                    'client_id' => $payment->client_id,
                    'amount' => $payment->amount,
                    'method' => $payment->payment_method,
                ],
            ]
        );
    }

    /**
     * Scopes
     */
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAuditable(Builder $query, Model $model): Builder
    {
        return $query->where('auditable_type', get_class($model))
                     ->where('auditable_id', $model->id);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('action', 'like', $category . '.%');
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get action categories
     */
    public static function getActionCategories(): array
    {
        return [
            'booking' => 'Bookings',
            'payment' => 'Payments',
            'membership' => 'Memberships',
            'pack' => 'Class Packs',
            'client' => 'Clients',
        ];
    }
}
