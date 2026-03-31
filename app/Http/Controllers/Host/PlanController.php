<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
     * Show the plans & pricing page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $host = $user->host;

        return view('host.plans.index', [
            'host' => $host,
            'currentPlan' => $host->subscription_plan,
        ]);
    }
}
