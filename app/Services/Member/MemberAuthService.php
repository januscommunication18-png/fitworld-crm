<?php

namespace App\Services\Member;

use App\Mail\MemberActivationCode;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\Host;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Shared member (client) authentication orchestration used by the branded
 * client app API. The core primitives — activation codes, lockout counters,
 * verification flags — live on the Client model and are the same ones the
 * subdomain member portal uses, so both surfaces enforce identical rules.
 */
class MemberAuthService
{
    /**
     * Seconds until another OTP may be sent, or null when allowed now.
     * Limits per host+email using `max_otp_resend_per_hour`.
     */
    public function otpRetryAfter(Host $host, string $email): ?int
    {
        $max = (int) $host->getMemberPortalSetting('max_otp_resend_per_hour', 3);

        return RateLimiter::tooManyAttempts($this->otpKey($host, $email), $max)
            ? RateLimiter::availableIn($this->otpKey($host, $email))
            : null;
    }

    /**
     * Generate an activation code for the client and email it (logged in
     * EmailLog like the web portal). Records the rate-limit hit.
     */
    public function sendOtp(Host $host, Client $client): void
    {
        $expiryMinutes = (int) $host->getMemberPortalSetting('activation_code_expiry_minutes', 10);
        $code = $client->generateActivationCode($expiryMinutes);

        $emailLog = EmailLog::logEmail(
            recipientEmail: $client->email,
            subject: "Your Verification Code - {$host->studio_name}",
            bodyPreview: "Your verification code is: {$code}. This code expires in {$expiryMinutes} minutes.",
            hostId: $host->id,
            recipientName: $client->full_name,
        );

        try {
            Mail::to($client->email)->send(new MemberActivationCode($client, $code, $host));
            $emailLog->markAsSent();
        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
        }

        RateLimiter::hit($this->otpKey($host, $client->email), 3600);
    }

    /**
     * Verify an activation code; on success marks the portal email verified
     * (first time) and records the login. Lockout counting happens inside
     * Client::verifyActivationCode.
     */
    public function verifyOtp(Client $client, string $code): bool
    {
        if (! $client->verifyActivationCode($code)) {
            return false;
        }

        if (! $client->hasVerifiedPortalEmail()) {
            $client->markPortalEmailAsVerified();
        }

        $client->recordPortalLogin();

        return true;
    }

    /**
     * Password check (password login mode); records the login on success.
     */
    public function checkPassword(Client $client, string $password): bool
    {
        if (! $client->password || ! Hash::check($password, $client->password)) {
            return false;
        }

        $client->recordPortalLogin();

        return true;
    }

    private function otpKey(Host $host, string $email): string
    {
        return 'otp-send:'.$host->id.':'.strtolower($email);
    }
}
