<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminOtpCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP has been verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark OTP as verified
     */
    public function markVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Generate a new OTP for an email
     */
    public static function generate(string $email, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        // Invalidate any existing OTPs for this email
        self::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Verify an OTP code for an email
     */
    public static function verify(string $email, string $code): ?self
    {
        $otp = self::where('email', $email)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->markVerified();
            return $otp;
        }

        return null;
    }

    /**
     * Count recent failed attempts for rate limiting
     */
    public static function countRecentAttempts(string $email, int $minutes = 15): int
    {
        return self::where('email', $email)
            ->where('created_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now()->subDay())->delete();
    }
}
