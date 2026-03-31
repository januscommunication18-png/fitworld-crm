<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingWebController extends Controller
{
    /**
     * Show the onboarding wizard page.
     */
    public function index()
    {
        $user = auth()->user();
        $host = $user->host;

        // Redirect to signup if no host exists
        if (!$host) {
            return redirect()->route('signup');
        }

        // Redirect to signup if initial onboarding not complete
        if (!$host->onboarding_completed_at) {
            return redirect()->route('signup');
        }

        // Redirect to dashboard if post-signup onboarding is complete
        if ($host->hasCompletedPostSignupOnboarding()) {
            return redirect()->route('dashboard');
        }

        return view('host.onboarding', [
            'host' => $host,
            'user' => $user,
        ]);
    }
}
