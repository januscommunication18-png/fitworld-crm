<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalInventoryLog extends Model
{
    // Action constants
    const ACTION_BOOKED = 'booked';
    const ACTION_RETURNED = 'returned';
    const ACTION_DAMAGED = 'damaged';
    const ACTION_LOST = 'lost';
    const ACTION_ADJUSTMENT = 'adjustment';
    const ACTION_RESTOCK = 'restock';

    protected $fillable = [
        'rental_item_id',
        'rental_booking_id',
        'action',
        'quantity_change',
        'inventory_after',
        'notes',
        'user_id',
    ];

    /**
     * Relationships
     */
    public function rentalItem(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function rentalBooking(): BelongsTo
    {
        return $this->belongsTo(RentalBooking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helpers
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_BOOKED => 'Booked Out',
            self::ACTION_RETURNED => 'Returned',
            self::ACTION_DAMAGED => 'Returned (Damaged)',
            self::ACTION_LOST => 'Lost',
            self::ACTION_ADJUSTMENT => 'Manual Adjustment',
            self::ACTION_RESTOCK => 'Restocked',
        ];
    }

    /**
     * Formatted attributes
     */
    public function getFormattedActionAttribute(): string
    {
        return self::getActions()[$this->action] ?? $this->action;
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_BOOKED => 'badge-warning',
            self::ACTION_RETURNED => 'badge-success',
            self::ACTION_DAMAGED => 'badge-warning',
            self::ACTION_LOST => 'badge-error',
            self::ACTION_ADJUSTMENT => 'badge-info',
            self::ACTION_RESTOCK => 'badge-primary',
            default => 'badge-neutral',
        };
    }

    public function getFormattedQuantityChangeAttribute(): string
    {
        if ($this->quantity_change > 0) {
            return '+' . $this->quantity_change;
        }
        return (string) $this->quantity_change;
    }

    /**
     * Scopes
     */
    public function scopeForItem($query, int $rentalItemId)
    {
        return $query->where('rental_item_id', $rentalItemId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Create a manual adjustment log
     */
    public static function createAdjustment(RentalItem $item, int $change, string $notes, ?User $user = null): self
    {
        $inventoryAfter = $item->available_inventory + $change;

        $log = self::create([
            'rental_item_id' => $item->id,
            'action' => self::ACTION_ADJUSTMENT,
            'quantity_change' => $change,
            'inventory_after' => $inventoryAfter,
            'notes' => $notes,
            'user_id' => $user?->id,
        ]);

        $item->update([
            'available_inventory' => $inventoryAfter,
            'total_inventory' => $item->total_inventory + $change,
        ]);

        return $log;
    }

    /**
     * Create a restock log
     */
    public static function createRestock(RentalItem $item, int $quantity, ?string $notes = null, ?User $user = null): self
    {
        $inventoryAfter = $item->available_inventory + $quantity;

        $log = self::create([
            'rental_item_id' => $item->id,
            'action' => self::ACTION_RESTOCK,
            'quantity_change' => $quantity,
            'inventory_after' => $inventoryAfter,
            'notes' => $notes ?? 'Restocked inventory',
            'user_id' => $user?->id,
        ]);

        $item->update([
            'available_inventory' => $inventoryAfter,
            'total_inventory' => $item->total_inventory + $quantity,
        ]);

        return $log;
    }
}
