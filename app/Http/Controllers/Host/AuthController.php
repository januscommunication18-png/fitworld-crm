<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
