<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingCredit extends Model
{
    protected $fillable = [
        'host_id',
        'client_id',
        'source_type',
        'source_id',
        'booking_id',
        'billing_period',
        'discount_percent',
        'amount_paid',
        'monthly_rate',
        'original_monthly_rate',
        'credit_remaining',
        'registration_fee_paid',
        'cancellation_fee_charged',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'refund_amount',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'monthly_rate' => 'decimal:2',
            'original_monthly_rate' => 'decimal:2',
            'credit_remaining' => 'decimal:2',
            'registration_fee_paid' => 'decimal:2',
            'cancellation_fee_charged' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXHAUSTED = 'exhausted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function bookingsPaidWith(): HasMany
    {
        return $this->hasMany(Booking::class, 'billing_credit_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('end_date', '>=', now()->toDateString())
            ->where('credit_remaining', '>', 0);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function isUsable(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->end_date->gte(now()->startOfDay())
            && (float) $this->credit_remaining > 0;
    }

    public function deduct(float $amount): float
    {
        $deducted = min($amount, (float) $this->credit_remaining);
        $this->credit_remaining = (float) $this->credit_remaining - $deducted;

        if ($this->credit_remaining <= 0) {
            $this->credit_remaining = 0;
            $this->status = self::STATUS_EXHAUSTED;
        }

        $this->save();

        return $deducted;
    }

    public function getSourcePlan(): ?Model
    {
        if ($this->source_type === 'class_plan') {
            return ClassPlan::find($this->source_id);
        }
        if ($this->source_type === 'service_plan') {
            return ServicePlan::find($this->source_id);
        }
        return null;
    }

    public function getSourceName(): string
    {
        return $this->getSourcePlan()?->name ?? ucfirst(str_replace('_', ' ', $this->source_type));
    }

    public function isWithinGracePeriod(): bool
    {
        $plan = $this->getSourcePlan();
        $graceHours = $plan?->cancellation_grace_hours ?? 48;

        return $this->created_at->addHours($graceHours)->isFuture();
    }

    public function calculateCancellationBreakdown(): array
    {
        $plan = $this->getSourcePlan();
        $withinGrace = $this->isWithinGracePeriod();
        $configuredFee = (float) ($plan?->cancellation_fee ?? 0);
        $creditRemaining = (float) $this->credit_remaining;
        $amountPaid = (float) $this->amount_paid;
        $regFeePaid = (float) $this->registration_fee_paid;

        if ($withinGrace) {
            return [
                'within_grace' => true,
                'cancellation_fee' => 0,
                'credit_remaining' => $creditRemaining,
                'refund' => $amountPaid + $regFeePaid,
                'net_owed_by_client' => 0,
                'registration_fee_refunded' => true,
            ];
        }

        $cancellationFee = $configuredFee;
        $refund = max(0, $creditRemaining - $cancellationFee);
        $netOwed = max(0, $cancellationFee - $creditRemaining);

        return [
            'within_grace' => false,
            'cancellation_fee' => $cancellationFee,
            'credit_remaining' => $creditRemaining,
            'credit_used' => $amountPaid - $creditRemaining,
            'refund' => $refund,
            'net_owed_by_client' => $netOwed,
            'registration_fee_refunded' => false,
        ];
    }

    public function cancel(int $cancelledBy, ?string $reason = null): array
    {
        $breakdown = $this->calculateCancellationBreakdown();

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
            'cancellation_fee_charged' => $breakdown['cancellation_fee'],
            'refund_amount' => $breakdown['refund'],
            'credit_remaining' => 0,
        ]);

        return $breakdown;
    }
}
