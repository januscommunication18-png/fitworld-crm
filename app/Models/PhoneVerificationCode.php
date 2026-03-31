<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVerificationCode extends Model
{
    protected $fillable = [
        'host_id',
        'phone_number',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /**
     * Get the host that owns this verification code.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    /**
     * Generate a new 6-digit verification code.
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification code for a host.
     */
    public static function createForHost(Host $host, string $phoneNumber, int $expiryMinutes = 10): self
    {
        // Invalidate any existing codes for this host
        static::where('host_id', $host->id)
            ->whereNull('verified_at')
            ->delete();

        return static::create([
            'host_id' => $host->id,
            'phone_number' => $phoneNumber,
            'code' => static::generateCode(),
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            'attempts' => 0,
        ]);
    }

    /**
     * Check if the code is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the code has been verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if max attempts have been reached.
     */
    public function hasMaxAttempts(int $maxAttempts = 5): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * Increment the attempt count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /**
     * Mark the code as verified.
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => Carbon::now()]);
    }

    /**
     * Verify a code for a host.
     */
    public static function verify(Host $host, string $code): bool
    {
        $verification = static::where('host_id', $host->id)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->first();

        if (!$verification) {
            return false;
        }

        if ($verification->isExpired()) {
            return false;
        }

        if ($verification->hasMaxAttempts()) {
            return false;
        }

        $verification->markAsVerified();

        return true;
    }

    /**
     * Count codes sent to a host in the last hour.
     */
    public static function countRecentCodesForHost(Host $host, int $hours = 1): int
    {
        return static::where('host_id', $host->id)
            ->where('created_at', '>=', Carbon::now()->subHours($hours))
            ->count();
    }
}
