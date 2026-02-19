<?php

namespace App\Http\Controllers\Subdomain;

use App\Http\Controllers\Controller;
use App\Mail\MemberActivationCode;
use App\Mail\MemberPasswordReset;
use App\Models\Client;
use App\Models\EmailLog;
use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class MemberAuthController extends Controller
{
    /**
     * Get the host from the request attributes (set by ResolveSubdomainHost middleware)
     */
    protected function getHost(Request $request): Host
    {
        return $request->attributes->get('subdomain_host');
    }

    /**
     * Get member portal settings for the host
     */
    protected function getPortalSettings(Host $host): array
    {
        return $host->member_portal_settings ?? Host::defaultMemberPortalSettings();
    }

    /**
     * Show the login page (handles both OTP and password modes)
     */
    public function showLogin(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        // Check if portal is enabled
        if (!($settings['enabled'] ?? false)) {
            return redirect()->route('subdomain.home', ['subdomain' => $host->subdomain])
                ->with('error', 'Member portal is not available.');
        }

        // If already logged in, redirect to portal
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.portal', ['subdomain' => $host->subdomain]);
        }

        return view('subdomain.member.login', [
            'host' => $host,
            'settings' => $settings,
            'loginMethod' => $settings['login_method'] ?? 'otp',
        ]);
    }

    /**
     * Show the sign up page
     */
    public function showSignup(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        // Check if portal is enabled
        if (!($settings['enabled'] ?? false)) {
            return redirect()->route('subdomain.home', ['subdomain' => $host->subdomain])
                ->with('error', 'Member portal is not available.');
        }

        // Check if self-registration is allowed
        if (!($settings['allow_self_registration'] ?? true)) {
            return redirect()->route('member.login', ['subdomain' => $host->subdomain])
                ->with('error', 'Self-registration is not available. Please contact the studio.');
        }

        // If already logged in, redirect to portal
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.portal', ['subdomain' => $host->subdomain]);
        }

        return view('subdomain.member.signup', [
            'host' => $host,
            'settings' => $settings,
        ]);
    }

    /**
     * Handle member sign up
     */
    public function signup(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        // Check if portal is enabled
        if (!($settings['enabled'] ?? false)) {
            return redirect()->route('subdomain.home', ['subdomain' => $host->subdomain])
                ->with('error', 'Member portal is not available.');
        }

        // Check if self-registration is allowed
        if (!($settings['allow_self_registration'] ?? true)) {
            return redirect()->route('member.login', ['subdomain' => $host->subdomain])
                ->with('error', 'Self-registration is not available.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Check if client already exists
        $existingClient = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        if ($existingClient) {
            // Client exists - redirect to login
            return redirect()->route('member.login', ['subdomain' => $host->subdomain])
                ->with('status', 'An account with this email already exists. Please sign in.')
                ->with('email', $validated['email']);
        }

        // Create new client
        $client = Client::create([
            'host_id' => $host->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'status' => Client::STATUS_LEAD,
            'source' => 'member_portal_signup',
        ]);

        // Generate and send OTP
        $expiryMinutes = $settings['activation_code_expiry_minutes'] ?? 10;
        $code = $client->generateActivationCode($expiryMinutes);

        // Log and send email
        $emailLog = EmailLog::logEmail(
            recipientEmail: $client->email,
            subject: "Verify Your Account - {$host->studio_name}",
            bodyPreview: "Your verification code is: {$code}. This code expires in {$expiryMinutes} minutes.",
            hostId: $host->id,
            recipientName: $client->full_name
        );

        try {
            Mail::to($client->email)->send(new MemberActivationCode($client, $code, $host));
            $emailLog->markAsSent();
        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
        }

        return redirect()
            ->route('member.verify-otp', ['subdomain' => $host->subdomain])
            ->with('email', $validated['email'])
            ->with('status', 'Account created! We sent a verification code to your email.');
    }

    /**
     * Handle OTP request (send activation code)
     */
    public function sendOtp(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Check rate limiting
        $key = 'otp-send:' . $host->id . ':' . $validated['email'];
        $maxResends = $settings['max_otp_resend_per_hour'] ?? 3;

        if (RateLimiter::tooManyAttempts($key, $maxResends)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->withErrors(['email' => "Too many code requests. Please try again in {$seconds} seconds."]);
        }

        // Find or create client
        $client = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        if (!$client) {
            // Return generic message to prevent email enumeration
            return back()
                ->with('status', 'If an account exists with that email, you will receive a code shortly.')
                ->with('email', $validated['email']);
        }

        // Check if locked out
        if (!$client->canAttemptOtp()) {
            $minutes = $client->getOtpLockoutMinutesRemaining();
            return back()
                ->withInput()
                ->withErrors(['email' => "Account temporarily locked. Please try again in {$minutes} minutes."]);
        }

        // Generate and send code
        $expiryMinutes = $settings['activation_code_expiry_minutes'] ?? 10;
        $code = $client->generateActivationCode($expiryMinutes);

        // Log and send email
        $emailLog = EmailLog::logEmail(
            recipientEmail: $client->email,
            subject: "Your Verification Code - {$host->studio_name}",
            bodyPreview: "Your verification code is: {$code}. This code expires in {$expiryMinutes} minutes.",
            hostId: $host->id,
            recipientName: $client->full_name
        );

        try {
            Mail::to($client->email)->send(new MemberActivationCode($client, $code, $host));
            $emailLog->markAsSent();
        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
        }

        // Record rate limit hit
        RateLimiter::hit($key, 3600); // 1 hour window

        return redirect()
            ->route('member.verify-otp', ['subdomain' => $host->subdomain])
            ->with('email', $validated['email'])
            ->with('status', 'We sent a verification code to your email.');
    }

    /**
     * Show OTP verification page
     */
    public function showVerifyOtp(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        $email = session('email') ?? $request->get('email');

        if (!$email) {
            return redirect()->route('member.login');
        }

        return view('subdomain.member.verify-otp', [
            'host' => $host,
            'settings' => $settings,
            'email' => $email,
        ]);
    }

    /**
     * Verify OTP and log in
     */
    public function verifyOtp(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $client = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        if (!$client) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Invalid verification code.']);
        }

        // Check if locked out
        if (!$client->canAttemptOtp()) {
            $minutes = $client->getOtpLockoutMinutesRemaining();
            return redirect()->route('member.login')
                ->withErrors(['email' => "Account temporarily locked. Please try again in {$minutes} minutes."]);
        }

        // Verify the code
        $maxAttempts = $settings['max_login_attempts'] ?? 10;
        $lockoutMinutes = $settings['lockout_duration_minutes'] ?? 30;

        if (!$client->verifyActivationCode($validated['code'])) {
            // Check if now locked
            if (!$client->canAttemptOtp()) {
                return redirect()->route('member.login')
                    ->withErrors(['email' => "Too many failed attempts. Account locked for {$lockoutMinutes} minutes."]);
            }

            return back()
                ->with('email', $validated['email'])
                ->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        // Mark email as verified if first time
        if (!$client->hasVerifiedPortalEmail()) {
            $client->markPortalEmailAsVerified();
        }

        // Record login
        $client->recordPortalLogin();

        // Log in the member
        Auth::guard('member')->login($client, true);

        return redirect()->intended(route('member.portal', ['subdomain' => $host->subdomain]));
    }

    /**
     * Handle password login
     */
    public function login(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'member-login:' . $host->id . ':' . $validated['email'];
        $maxAttempts = $settings['max_login_attempts'] ?? 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->withErrors(['email' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        $client = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        if (!$client || !$client->password) {
            RateLimiter::hit($key);
            return back()
                ->withInput()
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        if (!Hash::check($validated['password'], $client->password)) {
            RateLimiter::hit($key);
            return back()
                ->withInput()
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        // Clear rate limiter on success
        RateLimiter::clear($key);

        // Record login
        $client->recordPortalLogin();

        // Log in
        Auth::guard('member')->login($client, $request->boolean('remember'));

        return redirect()->intended(route('member.portal', ['subdomain' => $host->subdomain]));
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(Request $request)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        return view('subdomain.member.forgot-password', [
            'host' => $host,
            'settings' => $settings,
        ]);
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        // Rate limiting
        $key = 'password-reset:' . $host->id . ':' . $validated['email'];

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Too many reset requests. Please try again later.']);
        }

        $client = Client::where('host_id', $host->id)
            ->where('email', strtolower($validated['email']))
            ->first();

        // Always show success message to prevent enumeration
        if ($client && $client->password) {
            $token = $client->generatePasswordResetToken();

            // Log and send email
            $emailLog = EmailLog::logEmail(
                recipientEmail: $client->email,
                subject: "Reset Your Password - {$host->studio_name}",
                bodyPreview: "Click the link to reset your password for your {$host->studio_name} account.",
                hostId: $host->id,
                recipientName: $client->full_name
            );

            try {
                Mail::to($client->email)->send(new MemberPasswordReset($client, $token, $host));
                $emailLog->markAsSent();
            } catch (\Exception $e) {
                $emailLog->markAsFailed($e->getMessage());
            }
        }

        RateLimiter::hit($key, 3600);

        return back()->with('status', 'If an account exists with that email, you will receive a password reset link.');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(Request $request, string $token)
    {
        $host = $this->getHost($request);
        $settings = $this->getPortalSettings($host);

        // Find client by token
        $client = Client::where('host_id', $host->id)
            ->where('password_reset_token', $token)
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$client) {
            return redirect()->route('member.forgot-password')
                ->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        return view('subdomain.member.reset-password', [
            'host' => $host,
            'settings' => $settings,
            'token' => $token,
            'email' => $client->email,
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $host = $this->getHost($request);

        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client = Client::where('host_id', $host->id)
            ->where('password_reset_token', $validated['token'])
            ->where('password_reset_expires_at', '>', now())
            ->first();

        if (!$client) {
            return redirect()->route('member.forgot-password')
                ->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        if (!$client->resetPassword($validated['token'], $validated['password'])) {
            return back()->withErrors(['password' => 'Failed to reset password.']);
        }

        return redirect()->route('member.login')
            ->with('status', 'Password reset successfully. You can now log in.');
    }

    /**
     * Log out
     */
    public function logout(Request $request)
    {
        $host = $this->getHost($request);

        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('subdomain.home', ['subdomain' => $host->subdomain]);
    }
}
