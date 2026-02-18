<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class WaitlistEntry extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_WAITING = 'waiting';
    const STATUS_OFFERED = 'offered';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'host_id',
        'class_request_id',
        'class_plan_id',
        'class_session_id',
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
        'status',
        'claim_token',
        'offered_at',
        'expires_at',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Relationships

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function classRequest(): BelongsTo
    {
        return $this->belongsTo(ClassRequest::class);
    }

    public function classPlan(): BelongsTo
    {
        return $this->belongsTo(ClassPlan::class);
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    public function scopeOffered(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OFFERED);
    }

    public function scopeClaimed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLAIMED);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_WAITING, self::STATUS_OFFERED]);
    }

    public function scopeForClassPlan(Builder $query, int $classPlanId): Builder
    {
        return $query->where('class_plan_id', $classPlanId);
    }

    public function scopeForClassSession(Builder $query, int $classSessionId): Builder
    {
        return $query->where('class_session_id', $classSessionId);
    }

    public function scopeGeneral(Builder $query): Builder
    {
        return $query->whereNull('class_session_id');
    }

    public function scopeSessionSpecific(Builder $query): Builder
    {
        return $query->whereNotNull('class_session_id');
    }

    // Status methods

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isOffered(): bool
    {
        return $this->status === self::STATUS_OFFERED;
    }

    public function isClaimed(): bool
    {
        return $this->status === self::STATUS_CLAIMED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsOffered(int $hoursValid = 24): bool
    {
        $this->status = self::STATUS_OFFERED;
        $this->offered_at = now();
        $this->expires_at = now()->addHours($hoursValid);
        $this->claim_token = bin2hex(random_bytes(32));
        return $this->save();
    }

    public function markAsClaimed(): bool
    {
        $this->status = self::STATUS_CLAIMED;
        $this->claimed_at = now();
        return $this->save();
    }

    public function markAsExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    public function markAsCancelled(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING => 'badge-info',
            self::STATUS_OFFERED => 'badge-warning',
            self::STATUS_CLAIMED => 'badge-success',
            self::STATUS_EXPIRED => 'badge-neutral',
            self::STATUS_CANCELLED => 'badge-error',
            default => 'badge-neutral',
        };
    }

    // URL helpers

    public function getClaimUrl(): ?string
    {
        if (!$this->claim_token || !$this->host) {
            return null;
        }

        return route('subdomain.waitlist-claim', [
            'subdomain' => $this->host->subdomain,
            'token' => $this->claim_token,
        ]);
    }

    public function isForSession(): bool
    {
        return $this->class_session_id !== null;
    }

    public function isGeneral(): bool
    {
        return $this->class_session_id === null;
    }

    // Static helpers

    public static function getStatuses(): array
    {
        return [
            self::STATUS_WAITING => 'Waiting',
            self::STATUS_OFFERED => 'Offered',
            self::STATUS_CLAIMED => 'Claimed',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
