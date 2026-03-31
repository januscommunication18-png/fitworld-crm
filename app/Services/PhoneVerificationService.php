<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class PhoneVerificationService
{
    protected ?Client $twilio = null;
    protected string $verifySid;
    protected bool $isConfigured = false;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->verifySid = config('services.twilio.verify_sid', '');

        if ($sid && $token && $this->verifySid) {
            try {
                $this->twilio = new Client($sid, $token);
                $this->isConfigured = true;
            } catch (Exception $e) {
                Log::error('Failed to initialize Twilio client', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Check if Twilio is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Send verification code to phone number using Twilio Verify API
     *
     * @param string $phoneNumber The phone number without country code
     * @param string $countryCode The country code (e.g., +1)
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendCode(string $phoneNumber, string $countryCode): array
    {
        if (!$this->isConfigured) {
            // In development mode without Twilio, simulate success
            Log::info('Twilio not configured, simulating phone verification send', [
                'phone' => $phoneNumber,
                'country_code' => $countryCode,
            ]);

            return [
                'success' => true,
                'message' => 'Verification code sent (development mode).',
                'dev_mode' => true,
            ];
        }

        $fullNumber = $this->formatPhoneNumber($phoneNumber, $countryCode);

        try {
            $verification = $this->twilio->verify->v2
                ->services($this->verifySid)
                ->verifications
                ->create($fullNumber, 'sms');

            Log::info('Phone verification code sent', [
                'phone' => $fullNumber,
                'status' => $verification->status,
            ]);

            return [
                'success' => $verification->status === 'pending',
                'message' => 'Verification code sent to your phone.',
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio verification send failed', [
                'phone' => $fullNumber,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message' => $this->getUserFriendlyError($e),
            ];
        }
    }

    /**
     * Verify the code entered by user using Twilio Verify API
     *
     * @param string $phoneNumber The phone number without country code
     * @param string $countryCode The country code (e.g., +1)
     * @param string $code The 6-digit verification code
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyCode(string $phoneNumber, string $countryCode, string $code): array
    {
        if (!$this->isConfigured) {
            // In development mode, accept any 6-digit code or specific test code
            $isValid = strlen($code) === 6 && ($code === '123456' || ctype_digit($code));

            Log::info('Twilio not configured, simulating phone verification check', [
                'phone' => $phoneNumber,
                'code' => $code,
                'valid' => $isValid,
            ]);

            return [
                'success' => $isValid,
                'message' => $isValid
                    ? 'Phone number verified (development mode).'
                    : 'Invalid verification code.',
                'dev_mode' => true,
            ];
        }

        $fullNumber = $this->formatPhoneNumber($phoneNumber, $countryCode);

        try {
            $verification = $this->twilio->verify->v2
                ->services($this->verifySid)
                ->verificationChecks
                ->create([
                    'to' => $fullNumber,
                    'code' => $code,
                ]);

            $isApproved = $verification->status === 'approved';

            Log::info('Phone verification check', [
                'phone' => $fullNumber,
                'status' => $verification->status,
                'approved' => $isApproved,
            ]);

            return [
                'success' => $isApproved,
                'message' => $isApproved
                    ? 'Phone number verified successfully.'
                    : 'Invalid verification code. Please try again.',
            ];
        } catch (TwilioException $e) {
            Log::error('Twilio verification check failed', [
                'phone' => $fullNumber,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message' => $this->getUserFriendlyError($e),
            ];
        }
    }

    /**
     * Send verification code and update user record
     */
    public function sendCodeForUser(User $user): array
    {
        if (!$user->phone || !$user->phone_country_code) {
            return [
                'success' => false,
                'message' => 'Phone number is required.',
            ];
        }

        $result = $this->sendCode($user->phone, $user->phone_country_code);

        if ($result['success']) {
            // Store a local code as backup/audit (Twilio manages the actual verification)
            $user->generatePhoneVerificationCode();
        }

        return $result;
    }

    /**
     * Verify code and update user record
     */
    public function verifyCodeForUser(User $user, string $code): array
    {
        if (!$user->phone || !$user->phone_country_code) {
            return [
                'success' => false,
                'message' => 'Phone number is required.',
            ];
        }

        if ($user->isPhoneVerificationLocked()) {
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new code.',
            ];
        }

        $result = $this->verifyCode($user->phone, $user->phone_country_code, $code);

        if ($result['success']) {
            $user->markPhoneAsVerified();
        } else {
            $user->incrementPhoneVerificationAttempts();
        }

        return $result;
    }

    /**
     * Format phone number with country code for Twilio
     */
    protected function formatPhoneNumber(string $phoneNumber, string $countryCode): string
    {
        // Ensure country code starts with +
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . $countryCode;
        }

        // Remove any non-digit characters from phone number except leading +
        $phoneNumber = preg_replace('/[^\d]/', '', $phoneNumber);

        return $countryCode . $phoneNumber;
    }

    /**
     * Get user-friendly error message from Twilio exception
     */
    protected function getUserFriendlyError(TwilioException $e): string
    {
        $code = $e->getCode();

        return match ($code) {
            60200 => 'Invalid phone number format.',
            60202 => 'Maximum verification attempts reached. Please try again later.',
            60203 => 'Maximum send attempts reached. Please try again in a few minutes.',
            60212 => 'Verification code has expired. Please request a new code.',
            20003 => 'Authentication failed. Please contact support.',
            20404 => 'Phone number not found.',
            21211 => 'Invalid phone number. Please check and try again.',
            21614 => 'This phone number cannot receive SMS.',
            default => 'Unable to send verification code. Please try again.',
        };
    }

    /**
     * Get available country codes for phone verification
     */
    public static function getCountryCodes(): array
    {
        return [
            '+1' => ['name' => 'United States / Canada', 'flag' => '🇺🇸'],
            '+44' => ['name' => 'United Kingdom', 'flag' => '🇬🇧'],
            '+61' => ['name' => 'Australia', 'flag' => '🇦🇺'],
            '+91' => ['name' => 'India', 'flag' => '🇮🇳'],
            '+49' => ['name' => 'Germany', 'flag' => '🇩🇪'],
            '+33' => ['name' => 'France', 'flag' => '🇫🇷'],
            '+39' => ['name' => 'Italy', 'flag' => '🇮🇹'],
            '+34' => ['name' => 'Spain', 'flag' => '🇪🇸'],
            '+81' => ['name' => 'Japan', 'flag' => '🇯🇵'],
            '+86' => ['name' => 'China', 'flag' => '🇨🇳'],
            '+82' => ['name' => 'South Korea', 'flag' => '🇰🇷'],
            '+55' => ['name' => 'Brazil', 'flag' => '🇧🇷'],
            '+52' => ['name' => 'Mexico', 'flag' => '🇲🇽'],
            '+971' => ['name' => 'UAE', 'flag' => '🇦🇪'],
            '+65' => ['name' => 'Singapore', 'flag' => '🇸🇬'],
            '+64' => ['name' => 'New Zealand', 'flag' => '🇳🇿'],
            '+27' => ['name' => 'South Africa', 'flag' => '🇿🇦'],
            '+31' => ['name' => 'Netherlands', 'flag' => '🇳🇱'],
            '+46' => ['name' => 'Sweden', 'flag' => '🇸🇪'],
            '+47' => ['name' => 'Norway', 'flag' => '🇳🇴'],
        ];
    }
}
