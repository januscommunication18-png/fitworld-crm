<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SpaceRental extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Deposit status constants
    const DEPOSIT_NOT_REQUIRED = 'not_required';
    const DEPOSIT_PENDING = 'pending';
    const DEPOSIT_PAID = 'paid';
    const DEPOSIT_PARTIALLY_REFUNDED = 'partially_refunded';
    const DEPOSIT_REFUNDED = 'refunded';
    const DEPOSIT_FORFEITED = 'forfeited';

    // Purpose constants (same as SpaceRentalConfig)
    const PURPOSE_PHOTO_SHOOT = 'photo_shoot';
    const PURPOSE_VIDEO_PRODUCTION = 'video_production';
    const PURPOSE_WORKSHOP = 'workshop';
    const PURPOSE_TRAINING = 'training';
    const PURPOSE_OTHER = 'other';

    protected $fillable = [
        'reference_number',
        'host_id',
        'space_rental_config_id',
        'client_id',
        'external_client_name',
        'external_client_email',
        'external_client_phone',
        'external_client_company',
        'purpose',
        'purpose_notes',
        'start_time',
        'end_time',
        'hourly_rate',
        'hours_booked',
        'subtotal',
        'tax_amount',
        'total_amount',
        'deposit_amount',
        'currency',
        'status',
        'waiver_signed',
        'waiver_signed_at',
        'waiver_signer_name',
        'waiver_signer_ip',
        'deposit_status',
        'deposit_paid_at',
        'deposit_refund_amount',
        'deposit_refund_reason',
        'deposit_refunded_at',
        'damage_reported',
        'damage_notes',
        'damage_charge',
        'internal_notes',
        'created_by_user_id',
        'confirmed_by_user_id',
        'cancelled_by_user_id',
        'completed_by_user_id',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'completed_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($rental) {
            if (empty($rental->reference_number)) {
                $rental->reference_number = self::generateReferenceNumber();
            }
        });

        static::created(function ($rental) {
            // Log initial status
            $rental->statusLogs()->create([
                'from_status' => null,
                'to_status' => $rental->status,
                'notes' => 'Space rental created',
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public static function generateReferenceNumber(): string
    {
        return 'SR-' . strtoupper(Str::ulid()->toBase32());
    }

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'hourly_rate' => 'decimal:2',
            'hours_booked' => 'decimal:1',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'deposit_refund_amount' => 'decimal:2',
            'damage_charge' => 'decimal:2',
            'waiver_signed' => 'boolean',
            'waiver_signed_at' => 'datetime',
            'damage_reported' => 'boolean',
            'deposit_paid_at' => 'datetime',
            'deposit_refunded_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(SpaceRentalConfig::class, 'space_rental_config_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SpaceRentalStatusLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Status transition methods
     */
    public function confirm(User $user, ?string $notes = null): void
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_by_user_id' => $user->id,
            'confirmed_at' => now(),
        ]);

        $this->logStatusChange($oldStatus, self::STATUS_CONFIRMED, $user, $notes ?? 'Rental confirmed');
    }

    public function startRental(User $user, ?string $notes = null): void
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        $this->logStatusChange($oldStatus, self::STATUS_IN_PROGRESS, $user, $notes ?? 'Rental started');
    }

    public function complete(User $user, bool $hasDamage = false, ?string $damageNotes = null, float $damageCharge = 0, ?string $notes = null): void
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by_user_id' => $user->id,
            'completed_at' => now(),
            'damage_reported' => $hasDamage,
            'damage_notes' => $damageNotes,
            'damage_charge' => $damageCharge,
        ]);

        $statusNote = $notes ?? 'Rental completed';
        if ($hasDamage) {
            $statusNote .= ' - Damage reported: ' . ($damageNotes ?? 'No details');
        }
        $this->logStatusChange($oldStatus, self::STATUS_COMPLETED, $user, $statusNote);
    }

    public function cancel(User $user, ?string $reason = null): void
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_by_user_id' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        $this->logStatusChange($oldStatus, self::STATUS_CANCELLED, $user, 'Rental cancelled: ' . ($reason ?? 'No reason provided'));
    }

    public function markWaiverSigned(string $signerName, ?string $ip = null): void
    {
        $this->update([
            'waiver_signed' => true,
            'waiver_signed_at' => now(),
            'waiver_signer_name' => $signerName,
            'waiver_signer_ip' => $ip,
        ]);
    }

    public function recordDepositPayment(): void
    {
        $this->update([
            'deposit_status' => self::DEPOSIT_PAID,
            'deposit_paid_at' => now(),
        ]);
    }

    public function refundDeposit(float $amount, ?string $reason = null): void
    {
        $isFullRefund = $amount >= $this->deposit_amount;

        $this->update([
            'deposit_status' => $isFullRefund ? self::DEPOSIT_REFUNDED : self::DEPOSIT_PARTIALLY_REFUNDED,
            'deposit_refund_amount' => $amount,
            'deposit_refund_reason' => $reason,
            'deposit_refunded_at' => now(),
        ]);
    }

    public function forfeitDeposit(?string $reason = null): void
    {
        $this->update([
            'deposit_status' => self::DEPOSIT_FORFEITED,
            'deposit_refund_reason' => $reason ?? 'Deposit forfeited due to damage or violation',
        ]);
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

    /**
     * Status checks
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function canBeConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canBeStarted(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function requiresWaiver(): bool
    {
        return $this->config?->requires_waiver ?? false;
    }

    public function isWaiverPending(): bool
    {
        return $this->requiresWaiver() && !$this->waiver_signed;
    }

    public function requiresDeposit(): bool
    {
        return $this->deposit_amount > 0;
    }

    public function isDepositPending(): bool
    {
        return $this->requiresDeposit() && $this->deposit_status === self::DEPOSIT_PENDING;
    }

    /**
     * Client info accessors
     */
    public function getClientNameAttribute(): string
    {
        if ($this->client) {
            return $this->client->full_name;
        }
        return $this->external_client_name ?? 'Unknown';
    }

    public function getClientEmailAttribute(): ?string
    {
        if ($this->client) {
            return $this->client->email;
        }
        return $this->external_client_email;
    }

    public function getClientPhoneAttribute(): ?string
    {
        if ($this->client) {
            return $this->client->phone;
        }
        return $this->external_client_phone;
    }

    public function getClientCompanyAttribute(): ?string
    {
        return $this->external_client_company;
    }

    public function isExternalClient(): bool
    {
        return $this->client_id === null && $this->external_client_name !== null;
    }

    /**
     * Time and duration accessors
     */
    public function getDurationHoursAttribute(): float
    {
        return (float) $this->hours_booked;
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) ($this->hours_booked * 60);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->start_time->format('l, M j, Y');
    }

    public function getFormattedDateTimeAttribute(): string
    {
        return $this->start_time->format('M j, Y g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    /**
     * Location display
     */
    public function getLocationDisplayAttribute(): string
    {
        if (!$this->config) {
            return 'Unknown';
        }

        if ($this->config->isRoomType() && $this->config->room) {
            return $this->config->room->name . ' @ ' . ($this->config->location?->name ?? 'Unknown');
        }

        return $this->config->location?->name ?? $this->config->name;
    }

    /**
     * Pricing accessors
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->tax_amount, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->total_amount, 2);
    }

    public function getFormattedDepositAttribute(): string
    {
        if (!$this->deposit_amount || $this->deposit_amount == 0) {
            return 'None';
        }
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->deposit_amount, 2);
    }

    public function getFormattedHourlyRateAttribute(): string
    {
        $symbol = MembershipPlan::getCurrencySymbol($this->currency);
        return $symbol . number_format($this->hourly_rate, 2) . '/hr';
    }

    /**
     * Status display helpers
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'badge-neutral',
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_CONFIRMED => 'badge-info',
            self::STATUS_IN_PROGRESS => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-error',
            default => 'badge-neutral',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getDepositStatusBadgeClassAttribute(): string
    {
        return match ($this->deposit_status) {
            self::DEPOSIT_NOT_REQUIRED => 'badge-neutral',
            self::DEPOSIT_PENDING => 'badge-warning',
            self::DEPOSIT_PAID => 'badge-success',
            self::DEPOSIT_PARTIALLY_REFUNDED => 'badge-info',
            self::DEPOSIT_REFUNDED => 'badge-info',
            self::DEPOSIT_FORFEITED => 'badge-error',
            default => 'badge-neutral',
        };
    }

    public function getFormattedDepositStatusAttribute(): string
    {
        return self::getDepositStatuses()[$this->deposit_status] ?? $this->deposit_status;
    }

    public function getPurposeIconAttribute(): string
    {
        return SpaceRentalConfig::getPurposeIcon($this->purpose);
    }

    public function getFormattedPurposeAttribute(): string
    {
        return SpaceRentalConfig::getPurposes()[$this->purpose] ?? $this->purpose;
    }

    /**
     * Static helpers
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getDepositStatuses(): array
    {
        return [
            self::DEPOSIT_NOT_REQUIRED => 'Not Required',
            self::DEPOSIT_PENDING => 'Pending',
            self::DEPOSIT_PAID => 'Paid',
            self::DEPOSIT_PARTIALLY_REFUNDED => 'Partially Refunded',
            self::DEPOSIT_REFUNDED => 'Refunded',
            self::DEPOSIT_FORFEITED => 'Forfeited',
        ];
    }

    public static function getPurposes(): array
    {
        return SpaceRentalConfig::getPurposes();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PENDING])
            ->where('start_time', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('start_time', $date);
    }

    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_time', [$start, $end])
              ->orWhereBetween('end_time', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->where('start_time', '<=', $start)
                     ->where('end_time', '>=', $end);
              });
        });
    }

    public function scopeForConfig($query, int $configId)
    {
        return $query->where('space_rental_config_id', $configId);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeNeedsAttention($query)
    {
        return $query->where(function ($q) {
            // Upcoming confirmed rentals that need waiver or deposit
            $q->where('status', self::STATUS_CONFIRMED)
              ->where('start_time', '<=', now()->addDays(2))
              ->where(function ($q2) {
                  $q2->where(function ($q3) {
                      // Needs waiver
                      $q3->whereHas('config', fn($c) => $c->where('requires_waiver', true))
                         ->where('waiver_signed', false);
                  })->orWhere(function ($q3) {
                      // Needs deposit
                      $q3->where('deposit_amount', '>', 0)
                         ->where('deposit_status', self::DEPOSIT_PENDING);
                  });
              });
        });
    }
}
