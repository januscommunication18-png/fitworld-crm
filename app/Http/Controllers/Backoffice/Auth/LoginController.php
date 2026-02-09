<?php

namespace App\Http\Controllers\Backoffice\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin(Request $request): View
    {
        $email = $request->session()->get('admin_otp_email');

        return view('backoffice.auth.login', [
            'email' => $email,
        ]);
    }

    /**
     * Handle login request
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower($credentials['email']);

        // Find admin user
        $admin = AdminUser::where('email', $email)->first();

        if (!$admin) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        if (!$admin->isActive()) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        if (!Hash::check($credentials['password'], $admin->password)) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        // Log in the admin
        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        // Update last login
        $admin->update(['last_login_at' => now()]);

        // Regenerate session
        $request->session()->regenerate();

        // Check if must change password
        if ($admin->must_change_password) {
            return redirect()->route('backoffice.password.change')
                ->with('warning', 'You must change your password before continuing.');
        }

        return redirect()->intended(route('backoffice.dashboard'))
            ->with('success', 'Welcome back, ' . $admin->first_name . '!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear OTP verification
        $request->session()->forget([
            'admin_otp_verified',
            'admin_otp_verified_at',
            'admin_otp_email',
        ]);

        return redirect()->route('backoffice.security')
            ->with('success', 'You have been logged out.');
    }
}
