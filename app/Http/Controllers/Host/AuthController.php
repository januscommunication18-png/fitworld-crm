<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('host.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login timestamp
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            // Check if user has multiple studios
            if ($user->hasMultipleHosts()) {
                return redirect()->route('select-studio');
            }

            // Set the current host in session for single-studio users
            $primaryHost = $user->getPrimaryHost();
            if ($primaryHost) {
                $user->setCurrentHost($primaryHost);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the studio selector page for multi-studio users
     */
    public function selectStudio()
    {
        $user = Auth::user();
        $hosts = $user->hosts()->get();

        // If user only has one studio, redirect to dashboard
        if ($hosts->count() <= 1) {
            if ($hosts->count() === 1) {
                $user->setCurrentHost($hosts->first());
            }
            return redirect()->route('dashboard');
        }

        return view('auth.select-studio', [
            'hosts' => $hosts,
        ]);
    }

    /**
     * Switch to a different studio
     */
    public function switchStudio(Request $request)
    {
        $request->validate([
            'host_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $host = $user->hosts()->where('hosts.id', $request->host_id)->first();

        if (!$host) {
            return back()->with('error', 'You do not have access to this studio.');
        }

        $user->setCurrentHost($host);

        return redirect()->route('dashboard')
            ->with('success', "Switched to {$host->studio_name}");
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('host.auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request, string $token)
    {
        return view('host.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[0-9]/',      // at least one number
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
