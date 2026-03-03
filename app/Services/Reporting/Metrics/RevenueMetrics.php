<?php

namespace App\Services\Reporting\Metrics;

use App\Models\Host;
use App\Models\Transaction;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueMetrics
{
    /**
     * Get revenue summary for dashboard (Today, MTD, YTD)
     */
    public function getSummary(Host $host): array
    {
        $today = Carbon::today();

        return [
            'today' => $this->getRevenueForPeriod($host, $today, $today),
            'mtd' => $this->getRevenueForPeriod($host, $today->copy()->startOfMonth(), $today),
            'ytd' => $this->getRevenueForPeriod($host, $today->copy()->startOfYear(), $today),
            'last_month' => $this->getRevenueForPeriod(
                $host,
                $today->copy()->subMonth()->startOfMonth(),
                $today->copy()->subMonth()->endOfMonth()
            ),
        ];
    }

    /**
     * Get revenue for a specific date range
     */
    public function getRevenueForPeriod(Host $host, Carbon $start, Carbon $end): array
    {
        $transactions = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()])
            ->get();

        $gross = $transactions->sum('total_amount');
        $refunds = $transactions->sum('refunded_amount');
        $discounts = $transactions->sum('discount_amount');
        $tax = $transactions->sum('tax_amount');

        return [
            'gross' => round($gross, 2),
            'net' => round($gross - $refunds, 2),
            'tax' => round($tax, 2),
            'refunds' => round($refunds, 2),
            'discounts' => round($discounts, 2),
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Get revenue by payment method
     */
    public function getByPaymentMethod(Host $host, Carbon $start, Carbon $end, ?string $type = null): array
    {
        $query = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($type) {
            $query->where('type', 'like', "%{$type}%");
        }

        return $query->selectRaw('payment_method, manual_method, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('payment_method', 'manual_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->manual_method ?? $item->payment_method,
                    'total' => round($item->total, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get revenue by transaction type
     */
    public function getByType(Host $host, Carbon $start, Carbon $end, ?string $paymentMethod = null): array
    {
        $query = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($paymentMethod) {
            $query->where(function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod)
                  ->orWhere('manual_method', $paymentMethod);
            });
        }

        return $query->selectRaw('type, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type')
            ->map(function ($item) {
                return [
                    'total' => round($item->total, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get outstanding invoices summary
     */
    public function getOutstandingInvoices(Host $host): array
    {
        $unpaid = Invoice::where('host_id', $host->id)
            ->whereIn('status', ['draft', 'sent'])
            ->get();

        $overdue = $unpaid->filter(fn($inv) => $inv->due_date && Carbon::parse($inv->due_date)->isPast());

        return [
            'count' => $unpaid->count(),
            'total' => round($unpaid->sum('total'), 2),
            'overdue_count' => $overdue->count(),
            'overdue_total' => round($overdue->sum('total'), 2),
        ];
    }

    /**
     * Get revenue chart data for trends
     */
    public function getChartData(Host $host, string $period = 'month', int $periods = 12): array
    {
        $end = Carbon::today()->endOfDay();

        // Determine period settings
        switch ($period) {
            case 'day':
                $start = $end->copy()->subDays($periods);
                $format = 'Y-m-d';
                $labelFormat = 'M j';
                $interval = '1 day';
                break;
            case 'week':
                $start = $end->copy()->subWeeks($periods);
                $format = 'Y-W';
                $labelFormat = 'M j';
                $interval = '1 week';
                break;
            case 'year':
                $start = $end->copy()->subYears($periods);
                $format = 'Y';
                $labelFormat = 'Y';
                $interval = '1 year';
                break;
            case 'month':
            default:
                $start = $end->copy()->subMonths($periods);
                $format = 'Y-m';
                $labelFormat = 'M Y';
                $interval = '1 month';
                break;
        }

        // Get transactions grouped by period
        $transactions = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->get()
            ->groupBy(fn($t) => Carbon::parse($t->paid_at)->format($format));

        // Generate all periods and fill with values or zero
        $labels = [];
        $values = [];
        $current = $start->copy();

        while ($current <= $end) {
            $key = $current->format($format);
            $labels[] = $current->format($labelFormat);
            $values[] = isset($transactions[$key])
                ? round($transactions[$key]->sum('total_amount'), 2)
                : 0;

            // Move to next period
            switch ($period) {
                case 'day':
                    $current->addDay();
                    break;
                case 'week':
                    $current->addWeek();
                    break;
                case 'year':
                    $current->addYear();
                    break;
                case 'month':
                default:
                    $current->addMonth();
                    break;
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    /**
     * Get daily revenue for a specific month
     */
    public function getDailyRevenue(Host $host, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $transactions = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->get()
            ->groupBy(fn($t) => Carbon::parse($t->paid_at)->format('Y-m-d'));

        $data = [];
        $current = $start->copy();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $data[] = [
                'date' => $key,
                'day' => $current->format('j'),
                'revenue' => isset($transactions[$key])
                    ? round($transactions[$key]->sum('total_amount'), 2)
                    : 0,
            ];
            $current->addDay();
        }

        return $data;
    }

    /**
     * Compare revenue between two periods
     */
    public function comparePeriods(Host $host, Carbon $currentStart, Carbon $currentEnd, Carbon $previousStart, Carbon $previousEnd): array
    {
        $current = $this->getRevenueForPeriod($host, $currentStart, $currentEnd);
        $previous = $this->getRevenueForPeriod($host, $previousStart, $previousEnd);

        $change = $previous['gross'] > 0
            ? round((($current['gross'] - $previous['gross']) / $previous['gross']) * 100, 1)
            : ($current['gross'] > 0 ? 100 : 0);

        return [
            'current' => $current,
            'previous' => $previous,
            'change_percent' => $change,
            'change_direction' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get revenue by catalog/product category
     */
    public function getByCatalog(Host $host, Carbon $start, Carbon $end, ?string $paymentMethod = null, ?string $type = null): array
    {
        $query = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($paymentMethod) {
            $query->where(function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod)
                  ->orWhere('manual_method', $paymentMethod);
            });
        }

        if ($type) {
            $query->where('type', 'like', "%{$type}%");
        }

        $transactions = $query->get();

        $categories = [
            'memberships' => ['total' => 0, 'count' => 0],
            'class_packs' => ['total' => 0, 'count' => 0],
            'drop_ins' => ['total' => 0, 'count' => 0],
            'products' => ['total' => 0, 'count' => 0],
            'other' => ['total' => 0, 'count' => 0],
        ];

        foreach ($transactions as $txn) {
            $type = $txn->type ?? 'other';

            if (str_contains($type, 'membership')) {
                $categories['memberships']['total'] += $txn->total_amount;
                $categories['memberships']['count']++;
            } elseif (str_contains($type, 'pack') || str_contains($type, 'class_pack')) {
                $categories['class_packs']['total'] += $txn->total_amount;
                $categories['class_packs']['count']++;
            } elseif (str_contains($type, 'drop_in') || str_contains($type, 'booking')) {
                $categories['drop_ins']['total'] += $txn->total_amount;
                $categories['drop_ins']['count']++;
            } elseif (str_contains($type, 'product') || str_contains($type, 'retail')) {
                $categories['products']['total'] += $txn->total_amount;
                $categories['products']['count']++;
            } else {
                $categories['other']['total'] += $txn->total_amount;
                $categories['other']['count']++;
            }
        }

        // Round totals and filter out empty categories
        $result = [];
        foreach ($categories as $name => $data) {
            if ($data['count'] > 0) {
                $result[] = [
                    'name' => ucwords(str_replace('_', ' ', $name)),
                    'total' => round($data['total'], 2),
                    'count' => $data['count'],
                ];
            }
        }

        // Sort by total descending
        usort($result, fn($a, $b) => $b['total'] - $a['total']);

        return $result;
    }

    /**
     * Get revenue trend by type (stacked)
     */
    public function getChartDataByType(Host $host, Carbon $start, Carbon $end, int $periods = 12, ?string $paymentMethod = null, ?string $type = null): array
    {
        $periodLength = $start->diffInDays($end) / $periods;
        $labels = [];
        $memberships = [];
        $classPacks = [];
        $dropIns = [];
        $other = [];

        $current = $start->copy();
        $periodDays = max(1, (int) ceil($periodLength));

        for ($i = 0; $i < $periods; $i++) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->addDays($periodDays - 1)->endOfDay();

            if ($periodEnd > $end) {
                $periodEnd = $end->copy()->endOfDay();
            }

            $labels[] = $periodStart->format('M j');

            $query = Transaction::where('host_id', $host->id)
                ->paid()
                ->whereBetween('paid_at', [$periodStart->startOfDay(), $periodEnd]);

            if ($paymentMethod) {
                $query->where(function ($q) use ($paymentMethod) {
                    $q->where('payment_method', $paymentMethod)
                      ->orWhere('manual_method', $paymentMethod);
                });
            }

            if ($type) {
                $query->where('type', 'like', "%{$type}%");
            }

            $transactions = $query->get();

            $membershipTotal = 0;
            $classPackTotal = 0;
            $dropInTotal = 0;
            $otherTotal = 0;

            foreach ($transactions as $txn) {
                $type = $txn->type ?? 'other';

                if (str_contains($type, 'membership')) {
                    $membershipTotal += $txn->total_amount;
                } elseif (str_contains($type, 'pack') || str_contains($type, 'class_pack')) {
                    $classPackTotal += $txn->total_amount;
                } elseif (str_contains($type, 'drop_in') || str_contains($type, 'booking')) {
                    $dropInTotal += $txn->total_amount;
                } else {
                    $otherTotal += $txn->total_amount;
                }
            }

            $memberships[] = round($membershipTotal, 2);
            $classPacks[] = round($classPackTotal, 2);
            $dropIns[] = round($dropInTotal, 2);
            $other[] = round($otherTotal, 2);

            $current->addDays($periodDays);
        }

        return [
            'labels' => $labels,
            'memberships' => $memberships,
            'class_packs' => $classPacks,
            'drop_ins' => $dropIns,
            'other' => $other,
        ];
    }

    /**
     * Get revenue by day of week
     */
    public function getByDayOfWeek(Host $host, Carbon $start, Carbon $end, ?string $paymentMethod = null, ?string $type = null): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $result = [];

        foreach ($days as $index => $day) {
            $query = Transaction::where('host_id', $host->id)
                ->paid()
                ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereRaw('DAYOFWEEK(paid_at) = ?', [$index + 1]);

            if ($paymentMethod) {
                $query->where(function ($q) use ($paymentMethod) {
                    $q->where('payment_method', $paymentMethod)
                      ->orWhere('manual_method', $paymentMethod);
                });
            }

            if ($type) {
                $query->where('type', 'like', "%{$type}%");
            }

            $total = $query->sum('total_amount');

            $result[] = [
                'day' => $day,
                'total' => round($total, 2),
            ];
        }

        return $result;
    }

    /**
     * Get filtered chart data for date range
     */
    public function getFilteredChartData(Host $host, Carbon $start, Carbon $end, ?string $paymentMethod = null, ?string $type = null): array
    {
        $days = $start->diffInDays($end);

        // Determine grouping based on date range
        if ($days <= 14) {
            $format = 'Y-m-d';
            $labelFormat = 'M j';
            $increment = 'day';
        } elseif ($days <= 90) {
            $format = 'Y-W';
            $labelFormat = 'M j';
            $increment = 'week';
        } else {
            $format = 'Y-m';
            $labelFormat = 'M Y';
            $increment = 'month';
        }

        // Build base query
        $query = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($paymentMethod) {
            $query->where(function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod)
                  ->orWhere('manual_method', $paymentMethod);
            });
        }

        if ($type) {
            $query->where('type', 'like', "%{$type}%");
        }

        $transactions = $query->get()
            ->groupBy(fn($t) => Carbon::parse($t->paid_at)->format($format));

        // Generate all periods
        $labels = [];
        $values = [];
        $current = $start->copy();

        while ($current <= $end) {
            $key = $current->format($format);
            $labels[] = $current->format($labelFormat);
            $values[] = isset($transactions[$key])
                ? round($transactions[$key]->sum('total_amount'), 2)
                : 0;

            // Move to next period
            match ($increment) {
                'day' => $current->addDay(),
                'week' => $current->addWeek(),
                'month' => $current->addMonth(),
            };
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'total' => array_sum($values),
        ];
    }

    /**
     * Get filtered revenue data
     */
    public function getFilteredRevenue(Host $host, Carbon $start, Carbon $end, ?string $paymentMethod = null, ?string $type = null): array
    {
        $query = Transaction::where('host_id', $host->id)
            ->paid()
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()]);

        if ($paymentMethod) {
            $query->where(function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod)
                  ->orWhere('manual_method', $paymentMethod);
            });
        }

        if ($type) {
            $query->where('type', 'like', "%{$type}%");
        }

        $transactions = $query->get();

        $gross = $transactions->sum('total_amount');
        $refunds = $transactions->sum('refunded_amount');
        $tax = $transactions->sum('tax_amount');

        return [
            'gross' => round($gross, 2),
            'net' => round($gross - $refunds, 2),
            'tax' => round($tax, 2),
            'refunds' => round($refunds, 2),
            'transaction_count' => $transactions->count(),
        ];
    }
}
