<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ClassPassCreditLog extends Model
{
    use HasFactory;

    protected $table = 'class_pass_credit_logs';

    // Credit type constants
    const TYPE_BOOKING = 'booking';
    const TYPE_CANCELLATION_RESTORE = 'cancellation_restore';
    const TYPE_ADMIN_ADJUST = 'admin_adjust';
    const TYPE_ROLLOVER = 'rollover';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_EXPIRY_FORFEIT = 'expiry_forfeit';
    const TYPE_FREEZE_ADJUST = 'freeze_adjust';

    protected $fillable = [
        'class_pass_purchase_id',
        'booking_id',
        'credits_change',
        'credit_type',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'credits_change' => 'integer',
        ];
    }

    /**
     * Relationships
     */
    public function classPassPurchase(): BelongsTo
    {
        return $this->belongsTo(ClassPassPurchase::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Accessors
     */
    public function getIsDeductionAttribute(): bool
    {
        return $this->credits_change < 0;
    }

    public function getIsAdditionAttribute(): bool
    {
        return $this->credits_change > 0;
    }

    public function getAbsoluteChangeAttribute(): int
    {
        return abs($this->credits_change);
    }

    public function getCreditTypeDisplayAttribute(): string
    {
        return match ($this->credit_type) {
            self::TYPE_BOOKING => 'Class Booking',
            self::TYPE_CANCELLATION_RESTORE => 'Cancellation Refund',
            self::TYPE_ADMIN_ADJUST => 'Admin Adjustment',
            self::TYPE_ROLLOVER => 'Rollover',
            self::TYPE_TRANSFER_OUT => 'Transferred Out',
            self::TYPE_TRANSFER_IN => 'Transferred In',
            self::TYPE_EXPIRY_FORFEIT => 'Expired Credits',
            self::TYPE_FREEZE_ADJUST => 'Freeze/Unfreeze',
            default => ucfirst(str_replace('_', ' ', $this->credit_type)),
        };
    }

    public function getCreditTypeBadgeClassAttribute(): string
    {
        return match ($this->credit_type) {
            self::TYPE_BOOKING => 'badge-error',
            self::TYPE_CANCELLATION_RESTORE => 'badge-success',
            self::TYPE_ADMIN_ADJUST => 'badge-warning',
            self::TYPE_ROLLOVER => 'badge-info',
            self::TYPE_TRANSFER_OUT => 'badge-error',
            self::TYPE_TRANSFER_IN => 'badge-success',
            self::TYPE_EXPIRY_FORFEIT => 'badge-neutral',
            self::TYPE_FREEZE_ADJUST => 'badge-warning',
            default => 'badge-neutral',
        };
    }

    public function getChangeDisplayAttribute(): string
    {
        $sign = $this->credits_change >= 0 ? '+' : '';
        return $sign . $this->credits_change;
    }

    /**
     * Scopes
     */
    public function scopeForPurchase(Builder $query, $purchaseId): Builder
    {
        return $query->where('class_pass_purchase_id', $purchaseId);
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('credits_change', '<', 0);
    }

    public function scopeAdditions(Builder $query): Builder
    {
        return $query->where('credits_change', '>', 0);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('credit_type', $type);
    }

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Static helpers
     */
    public static function getCreditTypes(): array
    {
        return [
            self::TYPE_BOOKING => 'Class Booking',
            self::TYPE_CANCELLATION_RESTORE => 'Cancellation Refund',
            self::TYPE_ADMIN_ADJUST => 'Admin Adjustment',
            self::TYPE_ROLLOVER => 'Rollover',
            self::TYPE_TRANSFER_OUT => 'Transferred Out',
            self::TYPE_TRANSFER_IN => 'Transferred In',
            self::TYPE_EXPIRY_FORFEIT => 'Expired Credits',
            self::TYPE_FREEZE_ADJUST => 'Freeze/Unfreeze',
        ];
    }
}
