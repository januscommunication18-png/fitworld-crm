<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class OfferRedemption extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_APPLIED = 'applied';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_VOIDED = 'voided';

    // Channel constants
    const CHANNEL_ONLINE = 'online';
    const CHANNEL_FRONT_DESK = 'front_desk';
    const CHANNEL_APP = 'app';
    const CHANNEL_MANUAL = 'manual';

    protected $fillable = [
        'host_id',
        'offer_id',
        'client_id',
        'redeemable_type',
        'redeemable_id',
        'original_price',
        'discount_amount',
        'final_price',
        'currency',
        'channel',
        'promo_code_used',
        'payment_id',
        'invoice_id',
        'status',
        'completed_at',
        'refunded_at',
        'applied_by',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'completed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    // Relationships
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function redeemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    // Scopes
    public function scopeForHost(Builder $query, $hostId): Builder
    {
        return $query->where('host_id', $hostId);
    }

    public function scopeForOffer(Builder $query, $offerId): Builder
    {
        return $query->where('offer_id', $offerId);
    }

    public function scopeForClient(Builder $query, $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeNotVoided(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_VOIDED, self::STATUS_REFUNDED]);
    }

    // Helpers
    public static function getStatuses(): array
    {
        return [
            self::STATUS_APPLIED => 'Applied',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_VOIDED => 'Voided',
        ];
    }

    public static function getChannels(): array
    {
        return [
            self::CHANNEL_ONLINE => 'Online Booking',
            self::CHANNEL_FRONT_DESK => 'Front Desk',
            self::CHANNEL_APP => 'Mobile App',
            self::CHANNEL_MANUAL => 'Manual Override',
        ];
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markRefunded(): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
        ]);

        // Reverse the offer analytics
        $this->offer->decrement('total_redemptions');
        $this->offer->decrement('total_discount_given', $this->discount_amount);
        $this->offer->decrement('total_revenue_generated', $this->final_price);
    }

    public function markVoided(): void
    {
        $this->update(['status' => self::STATUS_VOIDED]);

        // Reverse the offer analytics
        $this->offer->decrement('total_redemptions');
        $this->offer->decrement('total_discount_given', $this->discount_amount);
    }

    /**
     * Get savings percentage
     */
    public function getSavingsPercentage(): float
    {
        if ($this->original_price <= 0) {
            return 0;
        }

        return round(($this->discount_amount / $this->original_price) * 100, 1);
    }
}
