<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get client statistics
        $stats = [
            'total_clients' => Host::count(),
            'active_clients' => Host::where('status', Host::STATUS_ACTIVE)->count(),
            'new_today' => Host::whereDate('created_at', Carbon::today())->count(),
            'pending_verify' => Host::where('status', Host::STATUS_PENDING_VERIFY)->count(),
            'inactive_clients' => Host::where('status', Host::STATUS_INACTIVE)->count(),
            'suspended_clients' => Host::where('status', Host::STATUS_SUSPENDED)->count(),
        ];

        // Recent signups (last 10)
        $recentSignups = Host::with('owner')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Monthly signup trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTrend[] = [
                'month' => $date->format('M'),
                'count' => Host::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];
        }

        return view('backoffice.dashboard', compact('stats', 'recentSignups', 'monthlyTrend'));
    }
}
