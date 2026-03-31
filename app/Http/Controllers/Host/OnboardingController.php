<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\TechnicalSupportRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $host = $user->host;

        // If support was requested, redirect to waiting screen
        if ($host->hasSupportRequested()) {
            return redirect()->route('host.support-waiting');
        }

        // If onboarding is complete, redirect to dashboard or plans
        if ($host->onboarding_completed_at) {
            // Check if they have a plan, if not redirect to plans
            if (!$host->subscription_plan) {
                return redirect()->route('host.plans.index');
            }
            return redirect()->route('dashboard');
        }

        return view('host.onboarding');
    }

    /**
     * Show the support waiting screen.
     */
    public function supportWaiting(Request $request): View
    {
        $user = $request->user();
        $host = $user->host;

        // Get the latest support request
        $supportRequest = TechnicalSupportRequest::where('host_id', $host->id)
            ->where('source', 'onboarding')
            ->latest()
            ->first();

        return view('host.support-waiting', [
            'supportRequest' => $supportRequest,
        ]);
    }
}
