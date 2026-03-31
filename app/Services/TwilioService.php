<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class TwilioService
{
    protected ?string $sid;
    protected ?string $token;
    protected ?string $fromNumber;
    protected bool $enabled;

    public function __construct()
    {
        $this->sid = config('services.twilio.sid');
        $this->token = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.from');
        $this->enabled = !empty($this->sid) && !empty($this->token) && !empty($this->fromNumber);
    }

    /**
     * Check if Twilio is configured and enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Send a verification code via SMS.
     */
    public function sendVerificationCode(string $toNumber, string $code): bool
    {
        if (!$this->enabled) {
            Log::warning('Twilio SMS not sent - service not configured', [
                'to' => $toNumber,
            ]);

            // In development, log the code to help with testing
            if (app()->environment('local')) {
                Log::info('Twilio verification code (dev mode)', [
                    'to' => $toNumber,
                    'code' => $code,
                ]);
                return true;
            }

            return false;
        }

        try {
            $client = new \Twilio\Rest\Client($this->sid, $this->token);

            $client->messages->create($toNumber, [
                'from' => $this->fromNumber,
                'body' => "Your FitStudioHQ verification code is: {$code}. This code expires in 10 minutes.",
            ]);

            Log::info('Twilio SMS sent successfully', [
                'to' => $toNumber,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Twilio SMS failed', [
                'to' => $toNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Format phone number to E.164 format.
     */
    public function formatPhoneNumber(string $phoneNumber, string $countryCode = '+1'): string
    {
        // Remove all non-digit characters except the leading +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // If it already starts with +, assume it's already formatted
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // If it starts with country code without +, add the +
        if (str_starts_with($cleaned, ltrim($countryCode, '+'))) {
            return '+' . $cleaned;
        }

        // Otherwise, prepend the country code
        return $countryCode . $cleaned;
    }

    /**
     * Validate phone number format (basic validation).
     */
    public function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Should be between 10 and 15 digits
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }
}
