<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RentalBooking extends Model
{
    use HasFactory, SoftDeletes;

    // Fulfillment status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PREPARED = 'prepared';
    const STATUS_HANDED_OUT = 'handed_out';
    const STATUS_RETURNED = 'returned';
    const STATUS_LOST = 'lost';

    // Condition constants
    const CONDITION_GOOD = 'good';
    const CONDITION_DAMAGED = 'damaged';
    const CONDITION_LOST = 'lost';

    protected $fillable = [
        'host_id',
        'rental_item_id',
        'client_id',
        'transaction_id',
        'request_id',
        'bookable_type',
        'bookable_id',
        'quantity',
        'unit_price',
        'total_price',
        'deposit_amount',
        'currency',
        'rental_date',
        'due_date',
        'fulfillment_status',
        'prepared_at',
        'handed_out_at',
        'returned_at',
        'prepared_by',
        'handed_out_by',
        'returned_by',
        'condition_on_return',
        'damage_notes',
        'damage_charge',
        'deposit_refunded',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->request_id)) {
                $booking->request_id = self::generateRequestId();
            }
        });

        static::created(function ($booking) {
            // Log initial status
            $booking->statusLogs()->create([
                'from_status' => null,
                'to_status' => $booking->fulfillment_status,
                'notes' => 'Rental request created',
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public static function generateRequestId(): string
    {
        return 'RNT-' . strtoupper(Str::ulid()->toBase32());
    }

    protected function casts(): array
    {
        return [
            'rental_date' => 'date',
            'due_date' => 'date',
            'prepared_at' => 'datetime',
            'handed_out_at' => 'datetime',
            'returned_at' => 'datetime',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'damage_charge' => 'decimal:2',
            'deposit_refunded' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function rentalItem(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function handedOutByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_out_by');
    }

    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(RentalInventoryLog::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RentalBookingStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Status transition methods
     */
    public function markPrepared(User $user, ?string $notes = null): void
    {
        $oldStatus = $this->fulfillment_status;

        $this->update([
            'fulfillment_status' => self::STATUS_PREPARED,
            'prepared_at' => now(),
            'prepared_by' => $user->id,
        ]);

        $this->logStatusChange($oldStatus, self::STATUS_PREPARED, $user, $notes ?? 'Item prepared for pickup');
    }

    public function markHandedOut(User $user, ?string $notes = null): void
    {
        $oldStatus = $this->fulfillment_status;

        $this->update([
            'fulfillment_status' => self::STATUS_HANDED_OUT,
            'handed_out_at' => now(),
            'handed_out_by' => $user->id,
        ]);

        // Log inventory out
        RentalInventoryLog::create([
            'rental_item_id' => $this->rental_item_id,
            'rental_booking_id' => $this->id,
            'action' => 'booked',
            'quantity_change' => -$this->quantity,
            'inventory_after' => $this->rentalItem->available_inventory - $this->quantity,
            'user_id' => $user->id,
        ]);

        $this->rentalItem->decrement('available_inventory', $this->quantity);

        $this->logStatusChange($oldStatus, self::STATUS_HANDED_OUT, $user, $notes ?? 'Item handed to customer');
    }

    public function markReturned(User $user, string $condition, ?string $notes = null, float $damageCharge = 0): void
    {
        $oldStatus = $this->fulfillment_status;

        $this->update([
            'fulfillment_status' => self::STATUS_RETURNED,
            'returned_at' => now(),
            'returned_by' => $user->id,
            'condition_on_return' => $condition,
            'damage_notes' => $notes,
            'damage_charge' => $damageCharge,
        ]);

        // Log inventory back in
        $action = $condition === self::CONDITION_DAMAGED ? 'damaged' : 'returned';
        RentalInventoryLog::create([
            'rental_item_id' => $this->rental_item_id,
            'rental_booking_id' => $this->id,
            'action' => $action,
            'quantity_change' => $this->quantity,
            'inventory_after' => $this->rentalItem->available_inventory + $this->quantity,
            'notes' => $notes,
            'user_id' => $user->id,
        ]);

        $this->rentalItem->increment('available_inventory', $this->quantity);

        $statusNote = $condition === self::CONDITION_GOOD
            ? 'Item returned in good condition'
            : 'Item returned with damage: ' . ($notes ?? 'No details');
        $this->logStatusChange($oldStatus, self::STATUS_RETURNED, $user, $statusNote);
    }

    public function markLost(User $user, ?string $notes = null): void
    {
        $oldStatus = $this->fulfillment_status;

        $this->update([
            'fulfillment_status' => self::STATUS_LOST,
            'returned_at' => now(),
            'returned_by' => $user->id,
            'condition_on_return' => self::CONDITION_LOST,
            'damage_notes' => $notes,
            'deposit_refunded' => false,
        ]);

        // Log as lost - doesn't return to inventory
        RentalInventoryLog::create([
            'rental_item_id' => $this->rental_item_id,
            'rental_booking_id' => $this->id,
            'action' => 'lost',
            'quantity_change' => 0,
            'inventory_after' => $this->rentalItem->available_inventory,
            'notes' => $notes,
            'user_id' => $user->id,
        ]);

        // Reduce total inventory since item is lost
        $this->rentalItem->decrement('total_inventory', $this->quantity);

        $this->logStatusChange($oldStatus, self::STATUS_LOST, $user, 'Item marked as lost: ' . ($notes ?? 'No details'));
    }

    public function updateStatus(string $newStatus, User $user, ?string $notes = null): void
    {
        $oldStatus = $this->fulfillment_status;

        if ($oldStatus === $newStatus) {
            return;
        }

        // Handle status-specific logic
        switch ($newStatus) {
            case self::STATUS_PREPARED:
                $this->markPrepared($user, $notes);
                return;
            case self::STATUS_HANDED_OUT:
                $this->markHandedOut($user, $notes);
                return;
            case self::STATUS_RETURNED:
                $this->markReturned($user, self::CONDITION_GOOD, $notes);
                return;
            case self::STATUS_LOST:
                $this->markLost($user, $notes);
                return;
        }

        // Generic status update for any other case
        $this->update(['fulfillment_status' => $newStatus]);
        $this->logStatusChange($oldStatus, $newStatus, $user, $notes);
    }

    protected function logStatusChange(string $fromStatus, string $toStatus, User $user, ?string $notes = null): void
    {
        $this->statusLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'updated_by' => $user->id,
        ]);
    }

    public function refundDeposit(): void
    {
        $this->update(['deposit_refunded' => true]);
    }

    /**
     * Status checks
     */
    public function isPending(): bool
    {
        return $this->fulfillment_status === self::STATUS_PENDING;
    }

    public function isPrepared(): bool
    {
        return $this->fulfillment_status === self::STATUS_PREPARED;
    }

    public function isHandedOut(): bool
    {
        return $this->fulfillment_status === self::STATUS_HANDED_OUT;
    }

    public function isReturned(): bool
    {
        return $this->fulfillment_status === self::STATUS_RETURNED;
    }

    public function isLost(): bool
    {
        return $this->fulfillment_status === self::STATUS_LOST;
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date || !$this->isHandedOut()) {
            return false;
        }
        return $this->due_date->isPast();
    }

    public function isActive(): bool
    {
        return in_array($this->fulfillment_status, [
            self::STATUS_PENDING,
            self::STATUS_PREPARED,
            self::STATUS_HANDED_OUT,
        ]);
    }

    public function isCompleted(): bool
    {
        return in_array($this->fulfillment_status, [
            self::STATUS_RETURNED,
            self::STATUS_LOST,
        ]);
    }

    /**
     * Formatted attributes
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->fulfillment_status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_PREPARED => 'badge-info',
            self::STATUS_HANDED_OUT => 'badge-primary',
            self::STATUS_RETURNED => 'badge-success',
            self::STATUS_LOST => 'badge-error',
            default => 'badge-neutral',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return self::getStatuses()[$this->fulfillment_status] ?? $this->fulfillment_status;
    }

    public function getConditionBadgeClassAttribute(): string
    {
        return match ($this->condition_on_return) {
            self::CONDITION_GOOD => 'badge-success',
            self::CONDITION_DAMAGED => 'badge-warning',
            self::CONDITION_LOST => 'badge-error',
            default => 'badge-neutral',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->total_price, 2);
    }

    public function getFormattedDepositAttribute(): string
    {
        if (!$this->deposit_amount || $this->deposit_amount == 0) {
            return 'None';
        }
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->deposit_amount, 2);
    }

    /**
     * Static helpers
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PREPARED => 'Prepared',
            self::STATUS_HANDED_OUT => 'Handed Out',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_LOST => 'Lost',
        ];
    }

    public static function getConditions(): array
    {
        return [
            self::CONDITION_GOOD => 'Good',
            self::CONDITION_DAMAGED => 'Damaged',
            self::CONDITION_LOST => 'Lost',
        ];
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('fulfillment_status', [
            self::STATUS_PENDING,
            self::STATUS_PREPARED,
            self::STATUS_HANDED_OUT,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('fulfillment_status', self::STATUS_PENDING);
    }

    public function scopePrepared($query)
    {
        return $query->where('fulfillment_status', self::STATUS_PREPARED);
    }

    public function scopeHandedOut($query)
    {
        return $query->where('fulfillment_status', self::STATUS_HANDED_OUT);
    }

    public function scopeOverdue($query)
    {
        return $query->where('fulfillment_status', self::STATUS_HANDED_OUT)
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today());
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('rental_date', $date);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
