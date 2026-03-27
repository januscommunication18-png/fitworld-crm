<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class PriceOverrideRequest extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Default expiry time in minutes
    const DEFAULT_EXPIRY_MINUTES = 30;

    protected $fillable = [
        'host_id',
        'location_id',
        'requested_by',
        'manager_id',
        'actioned_by',
        'bookable_type',
        'bookable_id',
        'client_id',
        'original_price',
        'requested_price',
        'discount_code',
        'reason',
        'confirmation_code',
        'status',
        'expires_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'requested_price' => 'decimal:2',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // ========== Relationships ==========

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ========== Scopes ==========

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '<=', now());
    }

    public function scopeForManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeForHost($query, int $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    // ========== Accessors ==========

    public function getDiscountAmountAttribute(): float
    {
        return $this->original_price - $this->requested_price;
    }

    public function getDiscountPercentageAttribute(): float
    {
        if ($this->original_price <= 0) {
            return 0;
        }
        return round((($this->original_price - $this->requested_price) / $this->original_price) * 100, 2);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->is_expired;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => $this->is_expired ? 'Expired' : 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => $this->is_expired ? 'badge-warning' : 'badge-info',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_REJECTED => 'badge-error',
            self::STATUS_EXPIRED => 'badge-warning',
            self::STATUS_CANCELLED => 'badge-neutral',
            default => 'badge-ghost',
        };
    }

    // ========== Static Helpers ==========

    /**
     * Generate a unique confirmation code
     */
    public static function generateConfirmationCode(): string
    {
        do {
            $code = 'PO-' . strtoupper(Str::random(5));
        } while (static::where('confirmation_code', $code)->exists());

        return $code;
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    // ========== Actions ==========

    /**
     * Approve the request
     */
    public function approve(User $approver): bool
    {
        if ($this->status !== self::STATUS_PENDING || $this->is_expired) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'actioned_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the request
     */
    public function reject(User $rejecter, ?string $reason = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'actioned_by' => $rejecter->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Mark as expired
     */
    public function markExpired(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);

        return true;
    }

    /**
     * Cancel the request
     */
    public function cancel(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        return true;
    }

    /**
     * Verify confirmation code
     */
    public function verifyCode(string $code): bool
    {
        return strtoupper($this->confirmation_code) === strtoupper($code);
    }

    // ========== Boot ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->confirmation_code)) {
                $request->confirmation_code = static::generateConfirmationCode();
            }
            if (empty($request->expires_at)) {
                $request->expires_at = now()->addMinutes(self::DEFAULT_EXPIRY_MINUTES);
            }
        });
    }
}
