<?php

namespace App\Services\Reporting\Metrics;

use App\Models\Host;
use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MembershipMetrics
{
    /**
     * Get membership summary for dashboard
     */
    public function getSummary(Host $host): array
    {
        $today = Carbon::today();
        $thirtyDaysAgo = $today->copy()->subDays(30);

        return [
            'active' => $this->getActiveCount($host),
            'new_30_days' => $this->getNewMembersCount($host, $thirtyDaysAgo, $today),
            'paused' => $this->getPausedCount($host),
            'cancelled_30_days' => $this->getCancelledCount($host, $thirtyDaysAgo, $today),
            'mrr' => $this->calculateMRR($host),
        ];
    }

    /**
     * Get count of active memberships
     */
    public function getActiveCount(Host $host): int
    {
        return CustomerMembership::where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->count();
    }

    /**
     * Get count of new members in a period
     */
    public function getNewMembersCount(Host $host, Carbon $start, Carbon $end): int
    {
        return CustomerMembership::where('host_id', $host->id)
            ->whereBetween('started_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();
    }

    /**
     * Get count of paused memberships
     */
    public function getPausedCount(Host $host): int
    {
        return CustomerMembership::where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_PAUSED)
            ->count();
    }

    /**
     * Get count of cancelled memberships in a period
     */
    public function getCancelledCount(Host $host, Carbon $start, Carbon $end): int
    {
        return CustomerMembership::where('host_id', $host->id)
            ->whereBetween('cancelled_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();
    }

    /**
     * Calculate Monthly Recurring Revenue (MRR)
     */
    public function calculateMRR(Host $host): float
    {
        $activeMemberships = CustomerMembership::where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->get();

        $mrr = 0;
        $currency = $host->default_currency ?? 'USD';

        foreach ($activeMemberships as $membership) {
            if (!$membership->membershipPlan) continue;

            $plan = $membership->membershipPlan;
            $price = $this->getPlanPrice($plan, $currency);

            // Convert yearly to monthly
            if ($plan->interval === 'yearly') {
                $mrr += $price / 12;
            } else {
                $mrr += $price;
            }
        }

        return round($mrr, 2);
    }

    /**
     * Get plan price for currency
     */
    private function getPlanPrice(MembershipPlan $plan, string $currency): float
    {
        // Check multi-currency prices first
        if ($plan->prices && isset($plan->prices[$currency])) {
            return (float) $plan->prices[$currency];
        }

        // Fall back to default price
        return (float) ($plan->price ?? 0);
    }

    /**
     * Calculate churn rate for a month
     */
    public function calculateChurnRate(Host $host, ?Carbon $month = null): float
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Members at start of month
        $startCount = CustomerMembership::where('host_id', $host->id)
            ->where('started_at', '<', $startOfMonth)
            ->where(function ($q) use ($startOfMonth) {
                $q->whereNull('cancelled_at')
                  ->orWhere('cancelled_at', '>=', $startOfMonth);
            })
            ->count();

        if ($startCount === 0) return 0;

        // Members who cancelled during month
        $churned = CustomerMembership::where('host_id', $host->id)
            ->whereBetween('cancelled_at', [$startOfMonth, $endOfMonth])
            ->count();

        return round(($churned / $startCount) * 100, 2);
    }

    /**
     * Get membership growth metrics
     */
    public function getGrowthMetrics(Host $host, int $months = 6): array
    {
        $data = [];
        $current = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $current->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $data[] = [
                'month' => $month->format('M Y'),
                'month_key' => $month->format('Y-m'),
                'new' => $this->getNewMembersCount($host, $startOfMonth, $endOfMonth),
                'cancelled' => $this->getCancelledCount($host, $startOfMonth, $endOfMonth),
                'churn_rate' => $this->calculateChurnRate($host, $month),
            ];
        }

        return $data;
    }

    /**
     * Get memberships by plan type
     */
    public function getByPlanType(Host $host): array
    {
        return CustomerMembership::where('host_id', $host->id)
            ->where('status', CustomerMembership::STATUS_ACTIVE)
            ->with('membershipPlan')
            ->get()
            ->groupBy(fn($m) => $m->membershipPlan?->name ?? 'Unknown')
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    /**
     * Get Average Revenue Per Member (ARPM)
     */
    public function calculateARPM(Host $host): float
    {
        $activeCount = $this->getActiveCount($host);

        if ($activeCount === 0) return 0;

        $mrr = $this->calculateMRR($host);

        return round($mrr / $activeCount, 2);
    }

    /**
     * Get membership status breakdown
     */
    public function getStatusBreakdown(Host $host): array
    {
        return CustomerMembership::where('host_id', $host->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get chart data for membership trends
     */
    public function getChartData(Host $host, int $months = 12): array
    {
        $labels = [];
        $activeData = [];
        $newData = [];
        $current = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $current->copy()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $labels[] = $month->format('M Y');

            // Count active at end of month
            $activeAtEnd = CustomerMembership::where('host_id', $host->id)
                ->where('started_at', '<=', $endOfMonth)
                ->where(function ($q) use ($endOfMonth) {
                    $q->whereNull('cancelled_at')
                      ->orWhere('cancelled_at', '>', $endOfMonth);
                })
                ->where(function ($q) use ($endOfMonth) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', $endOfMonth);
                })
                ->count();

            $activeData[] = $activeAtEnd;
            $newData[] = $this->getNewMembersCount($host, $startOfMonth, $endOfMonth);
        }

        return [
            'labels' => $labels,
            'active' => $activeData,
            'new' => $newData,
        ];
    }
}
