<?php

namespace App\Http\Controllers\Backoffice\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminOtpMail;
use App\Models\AdminOtpCode;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SecurityController extends Controller
{
    /**
     * Show the security email input page
     */
    public function show(): View
    {
        return view('backoffice.auth.security');
    }

    /**
     * Send OTP code to admin email
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);

        // Check if this email belongs to an admin user
        $admin = AdminUser::where('email', $email)->first();

        if (!$admin) {
            // Don't reveal if email exists or not for security
            return redirect()->route('backoffice.security.verify')
                ->with('email', $email)
                ->with('info', 'If this email is registered, you will receive a verification code.');
        }

        if (!$admin->isActive()) {
            return back()->withErrors(['email' => 'This account has been deactivated.']);
        }

        // Check rate limiting (max 5 attempts per 15 minutes)
        if (AdminOtpCode::countRecentAttempts($email) >= 5) {
            return back()->withErrors(['email' => 'Too many attempts. Please try again later.']);
        }

        // Generate and send OTP
        $otp = AdminOtpCode::generate(
            $email,
            $request->ip(),
            $request->userAgent()
        );

        // Send email with OTP
        Mail::to($email)->send(new AdminOtpMail($otp->code, $admin->first_name));

        return redirect()->route('backoffice.security.verify')
            ->with('email', $email)
            ->with('success', 'Verification code sent to your email.');
    }

    /**
     * Show the OTP verification page
     */
    public function showVerify(Request $request): View|RedirectResponse
    {
        $email = session('email') ?? $request->old('email');

        if (!$email) {
            return redirect()->route('backoffice.security');
        }

        return view('backoffice.auth.verify-otp', [
            'email' => $email,
        ]);
    }

    /**
     * Verify the OTP code
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower($request->email);
        $code = $request->code;

        // Verify OTP
        $otp = AdminOtpCode::verify($email, $code);

        if (!$otp) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        // Mark OTP as verified in session (use Unix timestamp for reliable serialization)
        $request->session()->put('admin_otp_verified', true);
        $request->session()->put('admin_otp_verified_at', time());
        $request->session()->put('admin_otp_email', $email);

        // Redirect to intended URL or login
        $intended = $request->session()->pull('admin_intended_url', route('backoffice.login'));

        return redirect($intended)
            ->with('success', 'Email verified. Please log in.');
    }
}
