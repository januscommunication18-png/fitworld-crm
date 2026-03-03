<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Services\Reporting\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Reports overview/index
     */
    public function index()
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $metrics = $this->reportingService->getDashboardMetrics($host);
        $quickStats = $this->reportingService->getQuickStats($host);

        return view('host.reports.index', [
            'metrics' => $metrics,
            'quickStats' => $quickStats,
        ]);
    }

    /**
     * Attendance Report
     */
    public function attendance(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $period = $request->get('period', '30');
        $classPlanId = $request->get('class_plan') ? (int) $request->get('class_plan') : null;
        $instructorId = $request->get('instructor') ? (int) $request->get('instructor') : null;

        $start = Carbon::now()->subDays((int) $period);
        $end = Carbon::now();

        // Get attendance metrics with filters
        $attendanceMetrics = $this->reportingService->attendance();

        // Get filtered summary
        $summary = $attendanceMetrics->getFilteredSummary($host, $start, $end, $classPlanId, $instructorId);

        // Get attendance data
        $attendanceData = $this->reportingService->getAttendanceReport($host, $start, $end);
        $attendanceData['summary'] = array_merge($attendanceData['summary'], $summary);

        // Get chart data with filters
        $chartData = $attendanceMetrics->getChartData($host, 12, $classPlanId, $instructorId);

        // Get additional chart data
        $byClass = $attendanceMetrics->getAttendanceByClass($host, $start, $end);
        $byInstructor = $attendanceMetrics->getAttendanceByInstructor($host, $start, $end);
        $byDayOfWeek = $attendanceMetrics->getAttendanceByDayOfWeek($host, $start, $end);
        $byHour = $attendanceMetrics->getAttendanceByHour($host, $start, $end);

        // Get filter options
        $classPlans = \App\Models\ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        $instructors = \App\Models\Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('host.reports.attendance', [
            'data' => $attendanceData,
            'chartData' => $chartData,
            'byClass' => $byClass,
            'byInstructor' => $byInstructor,
            'byDayOfWeek' => $byDayOfWeek,
            'byHour' => $byHour,
            'period' => $period,
            'classPlanId' => $classPlanId,
            'instructorId' => $instructorId,
            'classPlans' => $classPlans,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Revenue Report
     */
    public function revenue(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $period = $request->get('period', '30');
        $paymentMethod = $request->get('payment_method');
        $revenueType = $request->get('type');

        // Calculate date range based on period
        if ($period === 'year') {
            $start = Carbon::now()->startOfYear();
        } elseif ($period === 'quarter') {
            $start = Carbon::now()->startOfQuarter();
        } elseif ($period === 'month') {
            $start = Carbon::now()->startOfMonth();
        } else {
            $start = Carbon::now()->subDays((int) $period);
        }
        $end = Carbon::now();

        $revenueMetrics = $this->reportingService->revenue();

        // Get filtered summary if filters are applied
        if ($paymentMethod || $revenueType) {
            $filteredSummary = $revenueMetrics->getFilteredRevenue($host, $start, $end, $paymentMethod, $revenueType);
        } else {
            $filteredSummary = null;
        }

        $revenueData = $this->reportingService->getRevenueReport($host, $start, $end);

        // Override with filtered data if filters are applied
        if ($paymentMethod || $revenueType) {
            $revenueData['summary'] = $revenueMetrics->getFilteredRevenue($host, $start, $end, $paymentMethod, $revenueType);
            $revenueData['by_payment_method'] = $revenueMetrics->getByPaymentMethod($host, $start, $end, $revenueType);
            $revenueData['by_type'] = $revenueMetrics->getByType($host, $start, $end, $paymentMethod);
        }

        // Get chart data with filters
        $chartData = $revenueMetrics->getFilteredChartData($host, $start, $end, $paymentMethod, $revenueType);

        // Get additional chart data with filters
        $byCatalog = $revenueMetrics->getByCatalog($host, $start, $end, $paymentMethod, $revenueType);
        $byDayOfWeek = $revenueMetrics->getByDayOfWeek($host, $start, $end, $paymentMethod, $revenueType);
        $chartDataByType = $revenueMetrics->getChartDataByType($host, $start, $end, 12, $paymentMethod, $revenueType);

        // Get filter options
        $paymentMethods = \App\Models\Transaction::where('host_id', $host->id)
            ->paid()
            ->selectRaw('COALESCE(manual_method, payment_method) as method')
            ->distinct()
            ->pluck('method')
            ->filter()
            ->values();

        $revenueTypes = \App\Models\Transaction::where('host_id', $host->id)
            ->paid()
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();

        return view('host.reports.revenue', [
            'data' => $revenueData,
            'chartData' => $chartData,
            'chartDataByType' => $chartDataByType,
            'byCatalog' => $byCatalog,
            'byDayOfWeek' => $byDayOfWeek,
            'period' => $period,
            'paymentMethod' => $paymentMethod,
            'revenueType' => $revenueType,
            'paymentMethods' => $paymentMethods,
            'revenueTypes' => $revenueTypes,
            'currency' => $host->default_currency ?? 'USD',
        ]);
    }

    /**
     * Catalog Performance Report (Classes, Services, Memberships, Class Packs, Rentals)
     */
    public function classPerformance(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $period = $request->get('period', '30');
        $catalogType = $request->get('catalog_type', 'all');
        $classPlanId = $request->get('class_plan') ? (int) $request->get('class_plan') : null;
        $instructorId = $request->get('instructor') ? (int) $request->get('instructor') : null;

        $start = Carbon::now()->subDays((int) $period);
        $end = Carbon::now();

        // ========================================
        // 1. CLASS SESSIONS
        // ========================================
        $classSessionsData = $this->getClassSessionsPerformance($host, $start, $end, $classPlanId, $instructorId);

        // ========================================
        // 2. SERVICE BOOKINGS
        // ========================================
        $servicesData = $this->getServicesPerformance($host, $start, $end);

        // ========================================
        // 3. MEMBERSHIPS
        // ========================================
        $membershipsData = $this->getMembershipsPerformance($host, $start, $end);

        // ========================================
        // 4. CLASS PACKS
        // ========================================
        $classPacksData = $this->getClassPacksPerformance($host, $start, $end);

        // ========================================
        // 5. RENTALS (Rental Items)
        // ========================================
        $rentalsData = $this->getRentalsPerformance($host, $start, $end);

        // ========================================
        // 6. SPACE RENTALS (Studio Rentals)
        // ========================================
        $spaceRentalsData = $this->getSpaceRentalsPerformance($host, $start, $end);

        // ========================================
        // CATALOG OVERVIEW CHART
        // ========================================
        $catalogOverview = [
            ['name' => 'Classes', 'bookings' => $classSessionsData['summary']['total_bookings'], 'revenue' => $classSessionsData['summary']['revenue'] ?? 0],
            ['name' => 'Services', 'bookings' => $servicesData['summary']['total_bookings'], 'revenue' => $servicesData['summary']['revenue'] ?? 0],
            ['name' => 'Memberships', 'bookings' => $membershipsData['summary']['total_purchases'], 'revenue' => $membershipsData['summary']['revenue'] ?? 0],
            ['name' => 'Class Packs', 'bookings' => $classPacksData['summary']['total_purchases'], 'revenue' => $classPacksData['summary']['revenue'] ?? 0],
            ['name' => 'Rentals', 'bookings' => $rentalsData['summary']['total_bookings'], 'revenue' => $rentalsData['summary']['revenue'] ?? 0],
            ['name' => 'Space Rentals', 'bookings' => $spaceRentalsData['summary']['total_bookings'], 'revenue' => $spaceRentalsData['summary']['revenue'] ?? 0],
        ];

        $catalogOverviewChart = [
            'labels' => array_column($catalogOverview, 'name'),
            'bookings' => array_column($catalogOverview, 'bookings'),
            'revenue' => array_column($catalogOverview, 'revenue'),
        ];

        // Overall summary
        $overallSummary = [
            'total_bookings' => array_sum(array_column($catalogOverview, 'bookings')),
            'total_revenue' => array_sum(array_column($catalogOverview, 'revenue')),
            'class_sessions' => $classSessionsData['summary']['total_sessions'],
            'service_slots' => $servicesData['summary']['total_slots'],
            'active_memberships' => $membershipsData['summary']['active_count'] ?? 0,
            'class_packs_sold' => $classPacksData['summary']['total_purchases'],
        ];

        // Bookings trend by catalog type
        $catalogTrend = $this->getCatalogBookingsTrend($host, $start, $end);

        // Get filter options
        $classPlans = \App\Models\ClassPlan::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        $instructors = \App\Models\Instructor::where('host_id', $host->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('host.reports.class-performance', [
            'overallSummary' => $overallSummary,
            'catalogOverview' => $catalogOverview,
            'catalogOverviewChart' => $catalogOverviewChart,
            'catalogTrend' => $catalogTrend,
            'classSessionsData' => $classSessionsData,
            'servicesData' => $servicesData,
            'membershipsData' => $membershipsData,
            'classPacksData' => $classPacksData,
            'rentalsData' => $rentalsData,
            'spaceRentalsData' => $spaceRentalsData,
            'period' => $period,
            'catalogType' => $catalogType,
            'classPlanId' => $classPlanId,
            'instructorId' => $instructorId,
            'classPlans' => $classPlans,
            'instructors' => $instructors,
        ]);
    }

    /**
     * Get class sessions performance data
     */
    private function getClassSessionsPerformance($host, $start, $end, $classPlanId = null, $instructorId = null): array
    {
        $query = ClassSession::where('host_id', $host->id)
            ->whereBetween('start_time', [$start, $end])
            ->where('status', 'completed');

        if ($classPlanId) {
            $query->where('class_plan_id', $classPlanId);
        }
        if ($instructorId) {
            $query->where('primary_instructor_id', $instructorId);
        }

        $sessions = $query->with(['classPlan', 'primaryInstructor'])
            ->withCount([
                'bookings as completed_count' => fn($q) => $q->where('status', Booking::STATUS_COMPLETED),
                'bookings as no_show_count' => fn($q) => $q->where('status', Booking::STATUS_NO_SHOW),
            ])
            ->get();

        $totalBookings = $sessions->sum('completed_count');
        $totalCapacity = $sessions->sum('capacity');

        // Revenue from class bookings
        $revenue = \App\Models\Transaction::where('host_id', $host->id)
            ->where('type', 'class_booking')
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_amount');

        $byClass = $sessions->groupBy(fn($s) => $s->classPlan?->name ?? 'Unknown')
            ->map(function ($classSessions, $className) {
                return [
                    'name' => $className,
                    'sessions' => $classSessions->count(),
                    'bookings' => $classSessions->sum('completed_count'),
                    'capacity' => $classSessions->sum('capacity'),
                    'utilization' => $classSessions->sum('capacity') > 0
                        ? round(($classSessions->sum('completed_count') / $classSessions->sum('capacity')) * 100, 1) : 0,
                ];
            })->sortByDesc('bookings')->values()->take(10)->toArray();

        $byInstructor = $sessions->groupBy(fn($s) => $s->primaryInstructor?->name ?? 'TBA')
            ->map(function ($instSessions, $name) {
                return [
                    'name' => $name,
                    'sessions' => $instSessions->count(),
                    'bookings' => $instSessions->sum('completed_count'),
                ];
            })->sortByDesc('bookings')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_sessions' => $sessions->count(),
                'total_bookings' => $totalBookings,
                'total_capacity' => $totalCapacity,
                'utilization' => $totalCapacity > 0 ? round(($totalBookings / $totalCapacity) * 100, 1) : 0,
                'no_shows' => $sessions->sum('no_show_count'),
                'revenue' => round($revenue, 2),
            ],
            'byClass' => $byClass,
            'byInstructor' => $byInstructor,
        ];
    }

    /**
     * Get services performance data
     */
    private function getServicesPerformance($host, $start, $end): array
    {
        $slots = \App\Models\ServiceSlot::where('host_id', $host->id)
            ->whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['booked', 'completed'])
            ->with(['servicePlan', 'instructor'])
            ->withCount([
                'bookings as completed_count' => fn($q) => $q->where('status', Booking::STATUS_COMPLETED),
            ])
            ->get();

        $revenue = \App\Models\Transaction::where('host_id', $host->id)
            ->where('type', 'service_booking')
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_amount');

        $byService = $slots->groupBy(fn($s) => $s->servicePlan?->name ?? 'Unknown')
            ->map(function ($serviceSlots, $name) {
                return [
                    'name' => $name,
                    'slots' => $serviceSlots->count(),
                    'bookings' => $serviceSlots->sum('completed_count'),
                ];
            })->sortByDesc('bookings')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_slots' => $slots->count(),
                'total_bookings' => $slots->sum('completed_count'),
                'revenue' => round($revenue, 2),
            ],
            'byService' => $byService,
        ];
    }

    /**
     * Get memberships performance data
     */
    private function getMembershipsPerformance($host, $start, $end): array
    {
        $purchases = \App\Models\CustomerMembership::where('host_id', $host->id)
            ->whereBetween('created_at', [$start, $end])
            ->with('membershipPlan')
            ->get();

        $activeCount = \App\Models\CustomerMembership::where('host_id', $host->id)
            ->where('status', 'active')
            ->count();

        $revenue = \App\Models\Transaction::where('host_id', $host->id)
            ->where('type', 'membership_purchase')
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_amount');

        $byPlan = $purchases->groupBy(fn($m) => $m->membershipPlan?->name ?? 'Unknown')
            ->map(function ($memberships, $name) {
                return [
                    'name' => $name,
                    'purchases' => $memberships->count(),
                ];
            })->sortByDesc('purchases')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_purchases' => $purchases->count(),
                'active_count' => $activeCount,
                'revenue' => round($revenue, 2),
            ],
            'byPlan' => $byPlan,
        ];
    }

    /**
     * Get class packs performance data
     */
    private function getClassPacksPerformance($host, $start, $end): array
    {
        $purchases = \App\Models\ClassPassPurchase::where('host_id', $host->id)
            ->whereBetween('created_at', [$start, $end])
            ->with('classPass')
            ->get();

        $revenue = \App\Models\Transaction::where('host_id', $host->id)
            ->where('type', 'class_pack_purchase')
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_amount');

        $byPack = $purchases->groupBy(fn($p) => $p->classPass?->name ?? 'Unknown')
            ->map(function ($packPurchases, $name) {
                return [
                    'name' => $name,
                    'purchases' => $packPurchases->count(),
                    'credits_sold' => $packPurchases->sum(fn($p) => $p->classPass?->credits ?? 0),
                ];
            })->sortByDesc('purchases')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_purchases' => $purchases->count(),
                'total_credits_sold' => $purchases->sum(fn($p) => $p->classPass?->credits ?? 0),
                'revenue' => round($revenue, 2),
            ],
            'byPack' => $byPack,
        ];
    }

    /**
     * Get rentals performance data
     */
    private function getRentalsPerformance($host, $start, $end): array
    {
        $bookings = \App\Models\RentalBooking::where('host_id', $host->id)
            ->whereBetween('created_at', [$start, $end])
            ->with('rentalItem')
            ->get();

        $revenue = \App\Models\Transaction::where('host_id', $host->id)
            ->where('type', 'rental')
            ->paid()
            ->whereBetween('paid_at', [$start, $end])
            ->sum('total_amount');

        $byItem = $bookings->groupBy(fn($b) => $b->rentalItem?->name ?? 'Unknown')
            ->map(function ($itemBookings, $name) {
                return [
                    'name' => $name,
                    'bookings' => $itemBookings->count(),
                ];
            })->sortByDesc('bookings')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_bookings' => $bookings->count(),
                'revenue' => round($revenue, 2),
            ],
            'byItem' => $byItem,
        ];
    }

    /**
     * Get space rentals performance data
     */
    private function getSpaceRentalsPerformance($host, $start, $end): array
    {
        $rentals = \App\Models\SpaceRental::where('host_id', $host->id)
            ->whereBetween('created_at', [$start, $end])
            ->with('config')
            ->get();

        $revenue = $rentals->sum('total_price');

        $bySpace = $rentals->groupBy(fn($r) => $r->config?->name ?? 'Unknown')
            ->map(function ($spaceRentals, $name) {
                return [
                    'name' => $name,
                    'bookings' => $spaceRentals->count(),
                    'revenue' => round($spaceRentals->sum('total_price'), 2),
                ];
            })->sortByDesc('bookings')->values()->take(10)->toArray();

        return [
            'summary' => [
                'total_bookings' => $rentals->count(),
                'revenue' => round($revenue, 2),
            ],
            'bySpace' => $bySpace,
        ];
    }

    /**
     * Get catalog bookings trend by week
     */
    private function getCatalogBookingsTrend($host, $start, $end): array
    {
        $labels = [];
        $classes = [];
        $services = [];
        $memberships = [];
        $classPacks = [];
        $rentals = [];

        $current = $start->copy()->startOfWeek();
        while ($current <= $end) {
            $weekEnd = $current->copy()->endOfWeek();
            $labels[] = $current->format('M j');

            // Class bookings
            $classes[] = Booking::where('host_id', $host->id)
                ->where('bookable_type', ClassSession::class)
                ->where('status', Booking::STATUS_COMPLETED)
                ->whereBetween('booked_at', [$current, $weekEnd])
                ->count();

            // Service bookings
            $services[] = Booking::where('host_id', $host->id)
                ->where('bookable_type', \App\Models\ServiceSlot::class)
                ->where('status', Booking::STATUS_COMPLETED)
                ->whereBetween('booked_at', [$current, $weekEnd])
                ->count();

            // Membership purchases
            $memberships[] = \App\Models\CustomerMembership::where('host_id', $host->id)
                ->whereBetween('created_at', [$current, $weekEnd])
                ->count();

            // Class pack purchases
            $classPacks[] = \App\Models\ClassPassPurchase::where('host_id', $host->id)
                ->whereBetween('created_at', [$current, $weekEnd])
                ->count();

            // Rentals
            $rentals[] = \App\Models\RentalBooking::where('host_id', $host->id)
                ->whereBetween('created_at', [$current, $weekEnd])
                ->count();

            $current->addWeek();
        }

        return [
            'labels' => $labels,
            'classes' => $classes,
            'services' => $services,
            'memberships' => $memberships,
            'class_packs' => $classPacks,
            'rentals' => $rentals,
        ];
    }

    /**
     * Retention Report
     */
    public function retention(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $membershipData = $this->reportingService->getMembershipReport($host);
        $chartData = $this->reportingService->getMembershipChartData($host, 12);

        return view('host.reports.retention', [
            'data' => $membershipData,
            'chartData' => $chartData,
        ]);
    }

    /**
     * API endpoint for attendance chart
     */
    public function apiAttendanceChart(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();
        $weeks = $request->get('weeks', 12);

        return response()->json(
            $this->reportingService->getAttendanceChartData($host, (int) $weeks)
        );
    }

    /**
     * API endpoint for membership chart
     */
    public function apiMembershipChart(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();
        $months = $request->get('months', 12);

        return response()->json(
            $this->reportingService->getMembershipChartData($host, (int) $months)
        );
    }
}
