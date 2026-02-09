<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOtpVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if OTP has been verified in this session
        if (!$request->session()->get('admin_otp_verified', false)) {
            // Store the intended URL for redirect after OTP verification
            if (!$request->is('backoffice/security*')) {
                $request->session()->put('admin_intended_url', $request->url());
            }

            return redirect()->route('backoffice.security');
        }

        // Check if OTP session has expired (30 minutes of inactivity)
        $lastActivity = $request->session()->get('admin_otp_verified_at');
        if ($lastActivity && now()->diffInMinutes($lastActivity) > 30) {
            $request->session()->forget(['admin_otp_verified', 'admin_otp_verified_at']);
            return redirect()->route('backoffice.security')
                ->with('error', 'Your session has expired. Please verify again.');
        }

        // Update last activity timestamp
        $request->session()->put('admin_otp_verified_at', now());

        return $next($request);
    }
}
