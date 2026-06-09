<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Services\Reporting\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile staff dashboard. Host is resolved by the `studio.context` middleware
 * (X-Studio-Id header) and read from the request attributes.
 */
class DashboardController extends Controller
{
    public function __construct(private ReportingService $reporting) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\Host $host */
        $host = $request->attributes->get('currentHost');
        $today = Carbon::today();

        $todaysClasses = ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', $today)
            ->whereIn('status', ['published', 'completed'])
            ->with(['classPlan', 'primaryInstructor', 'location', 'bookings'])
            ->orderBy('start_time')
            ->get()
            ->map(function (ClassSession $s) {
                $bookings = $s->bookings;
                $booked = $bookings->whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_COMPLETED,
                ])->count();

                return [
                    'id' => $s->id,
                    'name' => $s->classPlan?->name ?? $s->title ?? 'Class',
                    'start_time' => $s->start_time?->toIso8601String(),
                    'end_time' => $s->end_time?->toIso8601String(),
                    'instructor' => $s->primaryInstructor?->name ?? 'TBA',
                    'location' => $s->location?->name ?? 'TBA',
                    'booked' => $booked,
                    'capacity' => (int) $s->capacity,
                    'checked_in' => $bookings->whereNotNull('checked_in_at')->count(),
                    'status' => $s->status,
                ];
            })
            ->values();

        $upcoming = Booking::where('host_id', $host->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_WAITLISTED])
            ->where('bookable_type', ClassSession::class)
            ->whereHas('bookable', fn ($q) => $q->where('start_time', '>', Carbon::now()))
            ->with('bookable')
            ->get();

        return response()->json([
            'data' => [
                'studio' => [
                    'id' => $host->id,
                    'name' => $host->studio_name,
                ],
                'metrics' => $this->safe(fn () => $this->reporting->getDashboardMetrics($host)),
                'quick_stats' => $this->safe(fn () => $this->reporting->getQuickStats($host)),
                'todays_classes' => $todaysClasses,
                'upcoming_summary' => [
                    'total' => $upcoming->count(),
                    'today' => $upcoming
                        ->filter(fn ($b) => $b->bookable?->start_time?->isToday())
                        ->count(),
                    'this_week' => $upcoming
                        ->filter(fn ($b) => $b->bookable?->start_time?->isCurrentWeek())
                        ->count(),
                ],
                'alerts' => $this->buildAlerts($host),
            ],
        ]);
    }

    /**
     * Lightweight alerts for mobile (mirrors the web alerts, without web URLs).
     *
     * @return array<int, array<string, string>>
     */
    private function buildAlerts($host): array
    {
        $alerts = [];

        try {
            $outstanding = $this->reporting->revenue()->getOutstandingInvoices($host);
            if (($outstanding['overdue_count'] ?? 0) > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => $outstanding['overdue_count'].' Overdue Invoices',
                    'message' => 'Total: $'.number_format($outstanding['overdue_total'] ?? 0, 2),
                ];
            }
        } catch (\Throwable $e) {
            // skip section on error
        }

        try {
            $lowAttendance = ClassSession::where('host_id', $host->id)
                ->where('start_time', '>', Carbon::now())
                ->where('start_time', '<', Carbon::now()->addDays(7))
                ->where('status', 'published')
                ->withCount(['bookings' => fn ($q) => $q->where('status', Booking::STATUS_CONFIRMED)])
                ->having('bookings_count', '<', 3)
                ->with('classPlan')
                ->get();

            foreach ($lowAttendance as $class) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Low Attendance: '.($class->classPlan?->name ?? 'Class'),
                    'message' => 'Only '.$class->bookings_count.' bookings for '
                        .$class->start_time->format('M j @ g:ia'),
                ];
            }
        } catch (\Throwable $e) {
            // skip section on error
        }

        try {
            $membership = $this->reporting->membership()->getSummary($host);
            if (($membership['cancelled_30_days'] ?? 0) > 0) {
                $alerts[] = [
                    'type' => 'error',
                    'title' => $membership['cancelled_30_days'].' Membership Cancellations',
                    'message' => 'In the last 30 days',
                ];
            }
            if (($membership['new_30_days'] ?? 0) > 0) {
                $alerts[] = [
                    'type' => 'success',
                    'title' => $membership['new_30_days'].' New Members',
                    'message' => 'Joined in the last 30 days',
                ];
            }
        } catch (\Throwable $e) {
            // skip section on error
        }

        return $alerts;
    }

    /**
     * Run a reporting call defensively — a reporting failure shouldn't 500 the
     * whole dashboard.
     */
    private function safe(callable $fn): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
