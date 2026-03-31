<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PhoneVerificationController extends Controller
{
    /**
     * Send verification code via SMS
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone_type' => 'required|in:studio,owner',
            'phone_number' => 'required|string|min:10',
        ]);

        $user = Auth::user();
        $phoneNumber = $this->normalizePhoneNumber($request->phone_number);

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache (expires in 10 minutes)
        $cacheKey = 'phone_otp_' . $user->id . '_' . md5($phoneNumber);
        Cache::put($cacheKey, [
            'otp' => $otp,
            'phone_type' => $request->phone_type,
            'phone_number' => $phoneNumber,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Store phone type for later use
        Cache::put('phone_type_' . $user->id, $request->phone_type, now()->addMinutes(10));

        // Send SMS (integrate with your SMS provider here)
        $sent = $this->sendSms($phoneNumber, $otp);

        if ($sent) {
            Log::info('Phone verification OTP sent', [
                'user_id' => $user->id,
                'phone_type' => $request->phone_type,
                'phone_last_4' => substr($phoneNumber, -4),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send verification code. Please try again.',
        ], 500);
    }

    /**
     * Verify the OTP code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $phoneNumber = $this->normalizePhoneNumber($request->phone_number);
        $cacheKey = 'phone_otp_' . $user->id . '_' . md5($phoneNumber);

        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new code.',
            ], 400);
        }

        // Check attempts
        if ($cached['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new code.',
            ], 429);
        }

        // Verify OTP
        if ($cached['otp'] !== $request->otp_code) {
            // Increment attempts
            $cached['attempts']++;
            Cache::put($cacheKey, $cached, now()->addMinutes(10));

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. Please try again.',
            ], 400);
        }

        // OTP is valid - mark phone as verified
        $user->update([
            'phone' => $phoneNumber,
            'phone_type' => $cached['phone_type'],
            'phone_verified_at' => now(),
        ]);

        // Clear cache
        Cache::forget($cacheKey);
        Cache::forget('phone_type_' . $user->id);

        Log::info('Phone verified successfully', [
            'user_id' => $user->id,
            'phone_type' => $cached['phone_type'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified successfully.',
        ]);
    }

    /**
     * Normalize phone number (remove spaces, dashes, etc.)
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Send SMS via provider
     * TODO: Integrate with Twilio, Nexmo, or other SMS provider
     */
    protected function sendSms(string $phoneNumber, string $otp): bool
    {
        // For development/testing - log the OTP
        if (app()->environment('local', 'development', 'testing')) {
            Log::info('DEV MODE - Phone OTP', [
                'phone' => $phoneNumber,
                'otp' => $otp,
            ]);
            return true;
        }

        // TODO: Implement actual SMS sending here
        // Example with Twilio:
        // try {
        //     $twilio = new \Twilio\Rest\Client(
        //         config('services.twilio.sid'),
        //         config('services.twilio.token')
        //     );
        //
        //     $twilio->messages->create($phoneNumber, [
        //         'from' => config('services.twilio.from'),
        //         'body' => "Your FitCRM verification code is: {$otp}. Valid for 10 minutes."
        //     ]);
        //
        //     return true;
        // } catch (\Exception $e) {
        //     Log::error('SMS sending failed', ['error' => $e->getMessage()]);
        //     return false;
        // }

        return true;
    }
}
