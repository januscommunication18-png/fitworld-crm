<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Event;
use App\Services\Reporting\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    public function index()
    {
        $user = Auth::user();

        // Redirect to signup if user hasn't completed onboarding
        if (!$user->host || !$user->host->onboarding_completed_at) {
            return redirect()->route('signup');
        }

        $host = $user->currentHost();

        // Get user role for this host
        $userRole = $user->getRoleForHost($host) ?? $user->role;
        $isOwnerOrAdmin = $user->isOwner($host) || $user->isAdmin($host);
        $isManager = $user->isManager($host);
        $isStaff = $user->isStaff($host);
        $isInstructor = $user->hasInstructorRole($host);

        // Check if setup checklist is complete (only for owner/admin)
        if ($isOwnerOrAdmin) {
            $checklist = $this->getSetupChecklist($user, $host);
            $completedCount = collect($checklist)->where('completed', true)->count();
            $totalCount = count($checklist);
            $progress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

            // Show setup checklist if not all tasks are complete and user hasn't skipped
            if ($progress < 100 && !$host->setup_completed_at) {
                return view('host.dashboard.setup-checklist', [
                    'host' => $host,
                    'checklist' => $checklist,
                    'completedCount' => $completedCount,
                    'totalCount' => $totalCount,
                    'progress' => $progress,
                ]);
            }
        }

        // Get dashboard data based on role
        $metrics = null;
        $quickStats = null;
        $revenueChart = null;
        $upcomingEvents = collect();

        // Owner, Admin, Manager get full metrics
        if ($isOwnerOrAdmin || $isManager) {
            $metrics = $this->reportingService->getDashboardMetrics($host);
            $quickStats = $this->reportingService->getQuickStats($host);

            // Only Owner and Admin see revenue data
            if ($isOwnerOrAdmin) {
                $revenueChart = $this->reportingService->getRevenueChartData($host, 'month');
            }

            // Get upcoming events
            $upcomingEvents = Event::forHost($host->id)
                ->published()
                ->upcoming()
                ->withCount('registeredAttendees')
                ->orderBy('start_datetime')
                ->limit(5)
                ->get();
        } elseif ($isStaff) {
            // Staff get limited metrics (no revenue)
            $metrics = $this->reportingService->getDashboardMetrics($host);
            $quickStats = $this->reportingService->getQuickStats($host);

            // Get upcoming events
            $upcomingEvents = Event::forHost($host->id)
                ->published()
                ->upcoming()
                ->withCount('registeredAttendees')
                ->orderBy('start_datetime')
                ->limit(5)
                ->get();
        } elseif ($isInstructor) {
            // Instructor gets only their own data
            $instructor = \App\Models\Instructor::where('host_id', $host->id)
                ->where('user_id', $user->id)
                ->first();

            if ($instructor) {
                // Get instructor-specific stats
                $quickStats = $this->getInstructorQuickStats($host, $instructor);
            }
        }

        return view('host.dashboard', [
            'metrics' => $metrics,
            'quickStats' => $quickStats,
            'revenueChart' => $revenueChart,
            'upcomingEvents' => $upcomingEvents,
            'currency' => $host->default_currency ?? 'USD',
            'userRole' => $userRole,
            'isOwnerOrAdmin' => $isOwnerOrAdmin,
            'canViewRevenue' => $isOwnerOrAdmin,
        ]);
    }

    /**
     * Get quick stats for instructor dashboard
     */
    protected function getInstructorQuickStats($host, $instructor): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Get instructor's classes today
        $todayClasses = ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', $today)
            ->where(function ($q) use ($instructor) {
                $q->where('primary_instructor_id', $instructor->id)
                    ->orWhereHas('backupInstructors', fn($q) => $q->where('instructors.id', $instructor->id));
            })
            ->count();

        // Get instructor's classes this week
        $weekClasses = ClassSession::where('host_id', $host->id)
            ->where('start_time', '>=', $thisWeek)
            ->where('start_time', '<', $thisWeek->copy()->addWeek())
            ->where(function ($q) use ($instructor) {
                $q->where('primary_instructor_id', $instructor->id)
                    ->orWhereHas('backupInstructors', fn($q) => $q->where('instructors.id', $instructor->id));
            })
            ->count();

        // Get total bookings for instructor's classes this month
        // First get the class session IDs where this instructor teaches
        $instructorSessionIds = ClassSession::where('host_id', $host->id)
            ->where('start_time', '>=', $thisMonth)
            ->where(function ($q) use ($instructor) {
                $q->where('primary_instructor_id', $instructor->id)
                    ->orWhereHas('backupInstructors', fn($q2) => $q2->where('instructors.id', $instructor->id));
            })
            ->pluck('id');

        $monthBookings = Booking::where('host_id', $host->id)
            ->where('bookable_type', ClassSession::class)
            ->whereIn('bookable_id', $instructorSessionIds)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->count();

        return [
            'today_classes' => $todayClasses,
            'week_classes' => $weekClasses,
            'month_bookings' => $monthBookings,
        ];
    }

    /**
     * Get setup checklist status for the host
     */
    protected function getSetupChecklist($user, $host): array
    {
        return [
            'verify_account' => [
                'completed' => $user->hasVerifiedEmail(),
                'label' => 'Verify Your Email',
                'route' => 'verification.notice',
            ],
            'studio_profile' => [
                'completed' => $this->isStudioProfileComplete($host),
                'label' => 'Complete Studio Profile',
                'route' => 'settings.studio.profile',
            ],
            'payment' => [
                'completed' => $this->isPaymentSetupComplete($host),
                'label' => 'Setup Payment System',
                'route' => 'settings.payments.settings',
            ],
            'location' => [
                'completed' => $host->locations()->exists(),
                'label' => 'Setup Location',
                'route' => 'settings.locations.index',
            ],
            'instructor' => [
                'completed' => $this->isInstructorSetupComplete($host),
                'label' => 'Setup Instructor / Staff',
                'route' => 'settings.team.instructors',
            ],
            'catalog' => [
                'completed' => $this->isCatalogSetupComplete($host),
                'label' => 'Classes and Services',
                'route' => 'catalog.index',
            ],
        ];
    }

    /**
     * Check if instructor setup is complete
     * At least one instructor must have a complete profile (time slots configured)
     */
    protected function isInstructorSetupComplete($host): bool
    {
        $instructors = $host->instructors;

        if ($instructors->isEmpty()) {
            return false;
        }

        // Check if at least one instructor has a complete profile
        return $instructors->contains(fn($instructor) => $instructor->isProfileComplete());
    }

    /**
     * Check if catalog (classes and services) is setup
     * At least one class plan or service plan must exist
     */
    protected function isCatalogSetupComplete($host): bool
    {
        return $host->classPlans()->exists() || $host->servicePlans()->exists();
    }

    /**
     * Check if studio profile is complete
     */
    protected function isStudioProfileComplete($host): bool
    {
        // Check required fields for a complete profile
        return !empty($host->studio_name)
            && !empty($host->timezone)
            && !empty($host->country);
    }

    /**
     * Check if payment setup is complete
     */
    protected function isPaymentSetupComplete($host): bool
    {
        // Complete if Stripe is connected OR payment settings have been configured
        if (!empty($host->stripe_account_id)) {
            return true;
        }

        // Check if payment settings have been configured
        $settings = $host->payment_settings;
        if (!empty($settings)) {
            // Consider complete if they've made any payment method choice
            return isset($settings['accept_cards']) || isset($settings['accept_cash']);
        }

        return false;
    }

    /**
     * Skip setup and go directly to dashboard
     */
    public function skipSetup()
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $host->update([
            'setup_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'You can complete your setup anytime from Settings.');
    }

    /**
     * API endpoint for dashboard data (for Vue components)
     */
    public function apiDashboard()
    {
        $user = Auth::user();
        $host = $user->currentHost();

        return response()->json([
            'metrics' => $this->reportingService->getDashboardMetrics($host),
            'quick_stats' => $this->reportingService->getQuickStats($host),
            'revenue_chart' => $this->reportingService->getRevenueChartData($host, 'month'),
        ]);
    }

    /**
     * API endpoint for revenue chart data
     */
    public function apiRevenueChart(string $period = 'month')
    {
        $user = Auth::user();
        $host = $user->currentHost();

        return response()->json(
            $this->reportingService->getRevenueChartData($host, $period)
        );
    }

    /**
     * Today's Classes page
     */
    public function todaysClasses()
    {
        $user = Auth::user();
        $host = $user->currentHost();
        $today = Carbon::today();

        $classes = ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', $today)
            ->whereIn('status', ['published', 'completed'])
            ->with(['classPlan', 'primaryInstructor', 'location', 'bookings.client'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                $allBookings = $session->bookings;

                // Count by status
                $confirmed = $allBookings->where('status', Booking::STATUS_CONFIRMED)->count();
                $completed = $allBookings->where('status', Booking::STATUS_COMPLETED)->count();
                $cancelled = $allBookings->where('status', Booking::STATUS_CANCELLED)->count();
                $noShow = $allBookings->where('status', Booking::STATUS_NO_SHOW)->count();
                $waitlisted = $allBookings->where('status', Booking::STATUS_WAITLISTED)->count();

                // Checked in count (has checked_in_at timestamp)
                $checkedIn = $allBookings->whereNotNull('checked_in_at')->count();

                // Active bookings (confirmed + completed)
                $activeBookings = $allBookings->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED]);
                $bookedCount = $activeBookings->count();

                return [
                    'id' => $session->id,
                    'name' => $session->classPlan?->name ?? $session->title ?? 'Class',
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'instructor' => $session->primaryInstructor?->name ?? 'TBA',
                    'location' => $session->location?->name ?? 'TBA',
                    'booked' => $bookedCount,
                    'capacity' => $session->capacity,
                    'status' => $session->status,
                    'utilization' => $session->capacity > 0
                        ? round(($bookedCount / $session->capacity) * 100)
                        : 0,
                    // Detailed counts
                    'confirmed' => $confirmed,
                    'completed' => $completed,
                    'checked_in' => $checkedIn,
                    'cancelled' => $cancelled,
                    'no_show' => $noShow,
                    'waitlisted' => $waitlisted,
                    'bookings' => $activeBookings->map(fn($b) => [
                        'id' => $b->id,
                        'client_name' => $b->client?->full_name ?? 'Unknown',
                        'status' => $b->status,
                        'checked_in_at' => $b->checked_in_at,
                    ]),
                ];
            });

        // Classes by hour (timeline)
        $hourlyDistribution = collect();
        for ($hour = 6; $hour <= 21; $hour++) {
            $classesInHour = $classes->filter(function ($class) use ($hour) {
                return $class['start_time']->hour === $hour;
            });
            $hourlyDistribution->push([
                'hour' => Carbon::createFromTime($hour)->format('g A'),
                'count' => $classesInHour->count(),
                'booked' => $classesInHour->sum('booked'),
                'checked_in' => $classesInHour->sum('checked_in'),
            ]);
        }

        // Utilization by class (for horizontal bar chart)
        $utilizationByClass = $classes->map(fn($c) => [
            'name' => $c['name'],
            'utilization' => $c['utilization'],
            'booked' => $c['booked'],
            'capacity' => $c['capacity'],
            'checked_in' => $c['checked_in'],
            'cancelled' => $c['cancelled'],
            'no_show' => $c['no_show'],
            'waitlisted' => $c['waitlisted'],
        ])->values();

        // Overall status breakdown for pie chart
        $statusBreakdown = [
            'checked_in' => $classes->sum('checked_in'),
            'confirmed' => $classes->sum('confirmed') - $classes->sum('checked_in'), // Confirmed but not checked in
            'completed' => $classes->sum('completed'),
            'cancelled' => $classes->sum('cancelled'),
            'no_show' => $classes->sum('no_show'),
            'waitlisted' => $classes->sum('waitlisted'),
        ];

        // By instructor
        $byInstructor = $classes->groupBy('instructor')
            ->map(fn($group, $name) => [
                'name' => $name,
                'classes' => $group->count(),
                'bookings' => $group->sum('booked'),
            ])
            ->sortByDesc('bookings')
            ->values();

        // Chart data
        $chartData = [
            'hourly' => [
                'labels' => $hourlyDistribution->pluck('hour')->toArray(),
                'classes' => $hourlyDistribution->pluck('count')->toArray(),
                'bookings' => $hourlyDistribution->pluck('booked')->toArray(),
                'checked_in' => $hourlyDistribution->pluck('checked_in')->toArray(),
            ],
            'utilization' => [
                'labels' => $utilizationByClass->pluck('name')->toArray(),
                'values' => $utilizationByClass->pluck('utilization')->toArray(),
                'booked' => $utilizationByClass->pluck('booked')->toArray(),
                'capacity' => $utilizationByClass->pluck('capacity')->toArray(),
                'checked_in' => $utilizationByClass->pluck('checked_in')->toArray(),
                'cancelled' => $utilizationByClass->pluck('cancelled')->toArray(),
                'no_show' => $utilizationByClass->pluck('no_show')->toArray(),
                'waitlisted' => $utilizationByClass->pluck('waitlisted')->toArray(),
            ],
            'byInstructor' => [
                'labels' => $byInstructor->pluck('name')->toArray(),
                'classes' => $byInstructor->pluck('classes')->toArray(),
                'bookings' => $byInstructor->pluck('bookings')->toArray(),
            ],
            'statusBreakdown' => $statusBreakdown,
        ];

        return view('host.dashboard.todays-classes', [
            'classes' => $classes,
            'date' => $today,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Upcoming Bookings page
     */
    public function upcomingBookings(Request $request)
    {
        $user = Auth::user();
        $host = $user->currentHost();

        $bookings = Booking::where('host_id', $host->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_WAITLISTED])
            ->where('bookable_type', ClassSession::class)
            ->whereHas('bookable', function ($q) {
                $q->where('start_time', '>', Carbon::now());
            })
            ->with(['client', 'bookable.classPlan', 'bookable.primaryInstructor'])
            ->orderBy('booked_at', 'desc')
            ->paginate(20);

        // Get all upcoming bookings for charts (not paginated)
        $allUpcomingBookings = Booking::where('host_id', $host->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_WAITLISTED])
            ->where('bookable_type', ClassSession::class)
            ->whereHas('bookable', function ($q) {
                $q->where('start_time', '>', Carbon::now());
            })
            ->with(['bookable.classPlan'])
            ->get();

        // Summary stats
        $summary = [
            'total' => $allUpcomingBookings->count(),
            'confirmed' => $allUpcomingBookings->where('status', Booking::STATUS_CONFIRMED)->count(),
            'waitlisted' => $allUpcomingBookings->where('status', Booking::STATUS_WAITLISTED)->count(),
            'today' => $allUpcomingBookings->filter(fn($b) => $b->bookable?->start_time?->isToday())->count(),
            'this_week' => $allUpcomingBookings->filter(fn($b) => $b->bookable?->start_time?->isCurrentWeek())->count(),
        ];

        // Bookings by day (next 7 days)
        $bookingsByDay = collect();
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);
            $count = $allUpcomingBookings->filter(function ($booking) use ($date) {
                return $booking->bookable?->start_time?->isSameDay($date);
            })->count();
            $bookingsByDay->push([
                'date' => $date->format('D'),
                'full_date' => $date->format('M j'),
                'count' => $count,
            ]);
        }

        // Bookings by class type
        $bookingsByClass = $allUpcomingBookings
            ->groupBy(fn($b) => $b->bookable?->classPlan?->name ?? 'Other')
            ->map(fn($group, $name) => [
                'name' => $name,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(6);

        // Chart data
        $chartData = [
            'byDay' => [
                'labels' => $bookingsByDay->pluck('date')->toArray(),
                'values' => $bookingsByDay->pluck('count')->toArray(),
                'fullDates' => $bookingsByDay->pluck('full_date')->toArray(),
            ],
            'byClass' => [
                'labels' => $bookingsByClass->pluck('name')->toArray(),
                'values' => $bookingsByClass->pluck('count')->toArray(),
            ],
        ];

        return view('host.dashboard.upcoming-bookings', [
            'bookings' => $bookings,
            'summary' => $summary,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Alerts & Reminders page
     */
    public function alerts()
    {
        $user = Auth::user();
        $host = $user->currentHost();

        // Get various alerts
        $alerts = collect();

        // Outstanding invoices
        $outstandingInvoices = $this->reportingService->revenue()->getOutstandingInvoices($host);
        if ($outstandingInvoices['overdue_count'] > 0) {
            $alerts->push([
                'type' => 'warning',
                'icon' => 'tabler--alert-triangle',
                'title' => $outstandingInvoices['overdue_count'] . ' Overdue Invoices',
                'message' => 'Total: $' . number_format($outstandingInvoices['overdue_total'], 2),
                'action_url' => route('payments.transactions'),
                'action_text' => 'View Invoices',
            ]);
        }

        // Low attendance classes (next 7 days)
        $lowAttendanceClasses = ClassSession::where('host_id', $host->id)
            ->where('start_time', '>', Carbon::now())
            ->where('start_time', '<', Carbon::now()->addDays(7))
            ->where('status', 'published')
            ->withCount(['bookings' => function ($q) {
                $q->where('status', Booking::STATUS_CONFIRMED);
            }])
            ->having('bookings_count', '<', 3)
            ->with('classPlan')
            ->get();

        foreach ($lowAttendanceClasses as $class) {
            $alerts->push([
                'type' => 'info',
                'icon' => 'tabler--users',
                'title' => 'Low Attendance: ' . ($class->classPlan?->name ?? 'Class'),
                'message' => 'Only ' . $class->bookings_count . ' bookings for ' . $class->start_time->format('M j @ g:ia'),
                'action_url' => route('class-sessions.show', $class->id),
                'action_text' => 'View Class',
            ]);
        }

        // Membership metrics
        $membershipMetrics = $this->reportingService->membership()->getSummary($host);
        if ($membershipMetrics['cancelled_30_days'] > 0) {
            $alerts->push([
                'type' => 'error',
                'icon' => 'tabler--user-minus',
                'title' => $membershipMetrics['cancelled_30_days'] . ' Membership Cancellations',
                'message' => 'In the last 30 days',
                'action_url' => route('clients.index'),
                'action_text' => 'View Members',
            ]);
        }

        // New members celebration
        if ($membershipMetrics['new_30_days'] > 0) {
            $alerts->push([
                'type' => 'success',
                'icon' => 'tabler--user-plus',
                'title' => $membershipMetrics['new_30_days'] . ' New Members',
                'message' => 'Joined in the last 30 days',
                'action_url' => route('clients.members'),
                'action_text' => 'View Members',
            ]);
        }

        // No-show rate alert
        $attendanceMetrics = $this->reportingService->attendance()->getSummary($host);
        if ($attendanceMetrics['no_show_rate'] > 10) {
            $alerts->push([
                'type' => 'warning',
                'icon' => 'tabler--clock-off',
                'title' => 'High No-Show Rate',
                'message' => $attendanceMetrics['no_show_rate'] . '% no-show rate in the last 30 days',
                'action_url' => null,
                'action_text' => null,
            ]);
        }

        return view('host.dashboard.alerts', [
            'alerts' => $alerts,
            'metrics' => [
                'outstanding' => $outstandingInvoices,
                'membership' => $membershipMetrics,
                'attendance' => $attendanceMetrics,
            ],
        ]);
    }
}
