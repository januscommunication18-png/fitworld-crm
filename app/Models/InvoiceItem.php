<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'itemable_type',
        'itemable_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total_price',
        'sort_order',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_price' => 'decimal:2',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Auto-calculate total if not set
            if (empty($item->total_price)) {
                $item->total_price = ($item->unit_price * $item->quantity) - $item->discount + $item->tax;
            }
        });

        static::updating(function ($item) {
            // Recalculate total on update
            $item->total_price = ($item->unit_price * $item->quantity) - $item->discount + $item->tax;
        });

        // After saving an item, recalculate invoice totals
        static::saved(function ($item) {
            $item->invoice?->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice?->recalculateTotals();
        });
    }

    /**
     * Relationships
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessors
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getFormattedDiscountAttribute(): string
    {
        return $this->discount > 0 ? '-$' . number_format($this->discount, 2) : '';
    }

    public function getFormattedTaxAttribute(): string
    {
        return $this->tax > 0 ? '$' . number_format($this->tax, 2) : '';
    }

    /**
     * Calculate and set total price
     */
    public function calculateTotal(): float
    {
        return ($this->unit_price * $this->quantity) - $this->discount + $this->tax;
    }

    /**
     * Create item from a class session booking
     */
    public static function fromClassSession(ClassSession $session, ?float $price = null): array
    {
        return [
            'itemable_type' => ClassSession::class,
            'itemable_id' => $session->id,
            'description' => $session->display_title . ' - ' . $session->start_time->format('M j, Y g:i A'),
            'quantity' => 1,
            'unit_price' => $price ?? $session->price ?? 0,
            'discount' => 0,
            'tax' => 0,
        ];
    }

    /**
     * Create item from a service slot booking
     */
    public static function fromServiceSlot(ServiceSlot $slot, ?float $price = null): array
    {
        return [
            'itemable_type' => ServiceSlot::class,
            'itemable_id' => $slot->id,
            'description' => ($slot->servicePlan?->name ?? 'Service') . ' - ' . $slot->start_time->format('M j, Y g:i A'),
            'quantity' => 1,
            'unit_price' => $price ?? $slot->price ?? 0,
            'discount' => 0,
            'tax' => 0,
        ];
    }

    /**
     * Create item from a membership plan purchase
     */
    public static function fromMembershipPlan(MembershipPlan $plan, ?float $price = null): array
    {
        return [
            'itemable_type' => MembershipPlan::class,
            'itemable_id' => $plan->id,
            'description' => $plan->name . ' Membership',
            'quantity' => 1,
            'unit_price' => $price ?? $plan->price ?? 0,
            'discount' => 0,
            'tax' => 0,
        ];
    }

    /**
     * Create item from a class pack purchase
     */
    public static function fromClassPack(ClassPack $pack, ?float $price = null): array
    {
        return [
            'itemable_type' => ClassPack::class,
            'itemable_id' => $pack->id,
            'description' => $pack->name . ' (' . $pack->class_count . ' classes)',
            'quantity' => 1,
            'unit_price' => $price ?? $pack->price ?? 0,
            'discount' => 0,
            'tax' => 0,
        ];
    }
}
