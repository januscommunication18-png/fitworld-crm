<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationController extends Controller
{
    /**
     * Cooldown period in seconds between resend attempts
     */
    protected int $cooldownSeconds = 60;

    /**
     * Maximum resend attempts per hour
     */
    protected int $maxAttemptsPerHour = 5;

    /**
     * Show the email verification notice page
     */
    public function notice(Request $request)
    {
        $user = $request->user();

        // If already verified, redirect to dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Calculate cooldown remaining
        $rateLimitKey = $this->getRateLimitKey($user);
        $cooldownRemaining = RateLimiter::availableIn($rateLimitKey);

        // Get remaining attempts
        $hourlyKey = $this->getHourlyRateLimitKey($user);
        $remainingAttempts = RateLimiter::remaining($hourlyKey, $this->maxAttemptsPerHour);

        return view('auth.verify-email', [
            'cooldownRemaining' => max(0, $cooldownRemaining),
            'remainingAttempts' => $remainingAttempts,
            'maxAttempts' => $this->maxAttemptsPerHour,
        ]);
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        // If already verified, redirect to dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Check hourly rate limit (max 5 per hour)
        $hourlyKey = $this->getHourlyRateLimitKey($user);
        if (RateLimiter::tooManyAttempts($hourlyKey, $this->maxAttemptsPerHour)) {
            $seconds = RateLimiter::availableIn($hourlyKey);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'email' => "Too many verification attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        // Check cooldown rate limit (1 per minute)
        $rateLimitKey = $this->getRateLimitKey($user);
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return back()->withErrors([
                'email' => "Please wait {$seconds} seconds before requesting another verification email.",
            ]);
        }

        // Record the attempts
        RateLimiter::hit($rateLimitKey, $this->cooldownSeconds);
        RateLimiter::hit($hourlyKey, 3600); // 1 hour

        // Send the verification email
        $user->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent! Please check your email.');
    }

    /**
     * Get the rate limit key for cooldown
     */
    protected function getRateLimitKey($user): string
    {
        return 'verify-email-cooldown:' . $user->id;
    }

    /**
     * Get the rate limit key for hourly limit
     */
    protected function getHourlyRateLimitKey($user): string
    {
        return 'verify-email-hourly:' . $user->id;
    }
}
