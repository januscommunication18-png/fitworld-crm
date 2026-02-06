<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect to signup if user hasn't completed onboarding
        if (!$user->host || !$user->host->onboarding_completed_at) {
            return redirect()->route('signup');
        }

        return view('host.dashboard');
    }
}
