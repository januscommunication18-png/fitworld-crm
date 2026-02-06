<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SignupController extends Controller
{
    public function index()
    {
        // If user is logged in and has completed onboarding, redirect to dashboard
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user has a host and onboarding is completed
            if ($user->host && $user->host->onboarding_completed_at) {
                return redirect()->route('dashboard');
            }
        }

        return view('host.signup');
    }
}
