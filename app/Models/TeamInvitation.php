<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvitation extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'host_id',
        'email',
        'first_name',
        'last_name',
        'role',
        'permissions',
        'instructor_id',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    /**
     * Get the full name
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Generate a unique invitation token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Check if invitation can be resent
     */
    public function canResend(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_EXPIRED]);
    }

    /**
     * Mark as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
    }

    /**
     * Revoke invitation
     */
    public function revoke(): void
    {
        $this->update(['status' => self::STATUS_REVOKED]);
    }

    /**
     * Regenerate token and extend expiry
     */
    public function regenerate(int $expiryDays = 7): void
    {
        $this->update([
            'token' => self::generateToken(),
            'status' => self::STATUS_PENDING,
            'expires_at' => now()->addDays($expiryDays),
        ]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_REVOKED => 'Revoked',
        ];
    }
}
