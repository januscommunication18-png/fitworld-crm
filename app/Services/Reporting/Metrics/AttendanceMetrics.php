<?php

namespace App\Services\Reporting\Metrics;

use App\Models\Host;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\ClassPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceMetrics
{
    /**
     * Get attendance summary for dashboard
     */
    public function getSummary(Host $host): array
    {
        $today = Carbon::today();
        $thirtyDaysAgo = $today->copy()->subDays(30);

        return [
            'attendance_rate' => $this->getAttendanceRate($host, $thirtyDaysAgo, $today),
            'no_show_rate' => $this->getNoShowRate($host, $thirtyDaysAgo, $today),
            'late_cancel_rate' => $this->getLateCancelRate($host, $thirtyDaysAgo, $today),
            'total_bookings' => $this->getTotalBookings($host, $thirtyDaysAgo, $today),
            'capacity_utilization' => $this->getCapacityUtilization($host, $thirtyDaysAgo, $today),
        ];
    }

    /**
     * Get attendance rate (completed / (completed + no_show))
     */
    public function getAttendanceRate(Host $host, Carbon $start, Carbon $end): float
    {
        $completed = Booking::where('host_id', $host->id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        $noShow = Booking::where('host_id', $host->id)
            ->where('status', Booking::STATUS_NO_SHOW)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        $total = $completed + $noShow;

        if ($total === 0) return 0;

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Get no-show rate
     */
    public function getNoShowRate(Host $host, Carbon $start, Carbon $end): float
    {
        $total = Booking::where('host_id', $host->id)
            ->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_NO_SHOW])
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        if ($total === 0) return 0;

        $noShow = Booking::where('host_id', $host->id)
            ->where('status', Booking::STATUS_NO_SHOW)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        return round(($noShow / $total) * 100, 1);
    }

    /**
     * Get late cancellation rate
     */
    public function getLateCancelRate(Host $host, Carbon $start, Carbon $end): float
    {
        $total = Booking::where('host_id', $host->id)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        if ($total === 0) return 0;

        $lateCancelled = Booking::where('host_id', $host->id)
            ->where('status', Booking::STATUS_CANCELLED)
            ->where('is_late_cancellation', true)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();

        return round(($lateCancelled / $total) * 100, 1);
    }

    /**
     * Get total bookings count
     */
    public function getTotalBookings(Host $host, Carbon $start, Carbon $end): int
    {
        return Booking::where('host_id', $host->id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->count();
    }

    /**
     * Get capacity utilization for classes
     */
    public function getCapacityUtilization(Host $host, Carbon $start, Carbon $end): float
    {
        $sessions = ClassSession::where('host_id', $host->id)
            ->whereBetween('start_time', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->with('confirmedBookings')
            ->get();

        if ($sessions->isEmpty()) return 0;

        $totalCapacity = $sessions->sum('capacity');
        $totalBooked = $sessions->sum(fn($s) => $s->confirmedBookings->count());

        if ($totalCapacity === 0) return 0;

        return round(($totalBooked / $totalCapacity) * 100, 1);
    }

    /**
     * Get today's classes with booking info
     */
    public function getTodaysClasses(Host $host): Collection
    {
        $today = Carbon::today();

        return ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', $today)
            ->whereIn('status', ['published', 'completed'])
            ->with(['classPlan', 'primaryInstructor', 'confirmedBookings'])
            ->orderBy('start_time')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'name' => $session->classPlan?->name ?? 'Class',
                    'time' => Carbon::parse($session->start_time)->format('g:i A'),
                    'instructor' => $session->primaryInstructor?->name ?? 'TBA',
                    'booked' => $session->confirmedBookings->count(),
                    'capacity' => $session->capacity,
                    'status' => $session->status,
                    'utilization' => $session->capacity > 0
                        ? round(($session->confirmedBookings->count() / $session->capacity) * 100)
                        : 0,
                ];
            });
    }

    /**
     * Get top performing class by attendance
     */
    public function getTopPerformingClass(Host $host, ?Carbon $start = null, ?Carbon $end = null): ?array
    {
        $start = $start ?? Carbon::now()->subDays(30);
        $end = $end ?? Carbon::now();

        $topClass = ClassSession::where('host_id', $host->id)
            ->whereBetween('start_time', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->withCount(['confirmedBookings'])
            ->with('classPlan')
            ->orderByDesc('confirmed_bookings_count')
            ->first();

        if (!$topClass) return null;

        return [
            'name' => $topClass->classPlan?->name ?? 'Unknown',
            'total_bookings' => $topClass->confirmed_bookings_count,
            'avg_utilization' => $topClass->capacity > 0
                ? round(($topClass->confirmed_bookings_count / $topClass->capacity) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get booking status breakdown
     */
    public function getBookingStatusBreakdown(Host $host, Carbon $start, Carbon $end): array
    {
        return Booking::where('host_id', $host->id)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get booking source breakdown (online vs staff)
     */
    public function getBookingSourceBreakdown(Host $host, Carbon $start, Carbon $end): array
    {
        return Booking::where('host_id', $host->id)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
            ->selectRaw('booking_source, COUNT(*) as count')
            ->groupBy('booking_source')
            ->pluck('count', 'booking_source')
            ->toArray();
    }

    /**
     * Get attendance chart data with optional filters
     */
    public function getChartData(Host $host, int $weeks = 12, ?int $classPlanId = null, ?int $instructorId = null): array
    {
        $labels = [];
        $attendanceData = [];
        $noShowData = [];
        $cancelledData = [];
        $current = Carbon::now();

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = $current->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $labels[] = $weekStart->format('M j');

            $baseQuery = Booking::where('host_id', $host->id)
                ->where('bookable_type', ClassSession::class)
                ->whereBetween('booked_at', [$weekStart, $weekEnd]);

            // Apply class plan filter using whereHasMorph for polymorphic relationship
            if ($classPlanId) {
                $baseQuery->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($classPlanId) {
                    $q->where('class_plan_id', $classPlanId);
                });
            }

            // Apply instructor filter using whereHasMorph for polymorphic relationship
            if ($instructorId) {
                $baseQuery->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($instructorId) {
                    $q->where('primary_instructor_id', $instructorId);
                });
            }

            $completed = (clone $baseQuery)->where('status', Booking::STATUS_COMPLETED)->count();
            $noShow = (clone $baseQuery)->where('status', Booking::STATUS_NO_SHOW)->count();
            $cancelled = (clone $baseQuery)->where('status', Booking::STATUS_CANCELLED)->count();

            $attendanceData[] = $completed;
            $noShowData[] = $noShow;
            $cancelledData[] = $cancelled;
        }

        return [
            'labels' => $labels,
            'attendance' => $attendanceData,
            'no_shows' => $noShowData,
            'cancelled' => $cancelledData,
        ];
    }

    /**
     * Get filtered attendance summary
     */
    public function getFilteredSummary(Host $host, Carbon $start, Carbon $end, ?int $classPlanId = null, ?int $instructorId = null): array
    {
        $baseQuery = Booking::where('host_id', $host->id)
            ->where('bookable_type', ClassSession::class)
            ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()]);

        // Apply class plan filter using whereHasMorph for polymorphic relationship
        if ($classPlanId) {
            $baseQuery->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($classPlanId) {
                $q->where('class_plan_id', $classPlanId);
            });
        }

        // Apply instructor filter using whereHasMorph for polymorphic relationship
        if ($instructorId) {
            $baseQuery->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($instructorId) {
                $q->where('primary_instructor_id', $instructorId);
            });
        }

        $completed = (clone $baseQuery)->where('status', Booking::STATUS_COMPLETED)->count();
        $noShow = (clone $baseQuery)->where('status', Booking::STATUS_NO_SHOW)->count();
        $cancelled = (clone $baseQuery)->where('status', Booking::STATUS_CANCELLED)->count();
        $confirmed = (clone $baseQuery)->where('status', Booking::STATUS_CONFIRMED)->count();
        $total = $completed + $noShow + $cancelled + $confirmed;

        $attendanceTotal = $completed + $noShow;
        $attendanceRate = $attendanceTotal > 0 ? round(($completed / $attendanceTotal) * 100, 1) : 0;
        $noShowRate = $attendanceTotal > 0 ? round(($noShow / $attendanceTotal) * 100, 1) : 0;
        $cancelRate = $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;

        return [
            'attendance_rate' => $attendanceRate,
            'no_show_rate' => $noShowRate,
            'cancel_rate' => $cancelRate,
            'total_bookings' => $total,
            'completed' => $completed,
            'no_show' => $noShow,
            'cancelled' => $cancelled,
            'confirmed' => $confirmed,
        ];
    }

    /**
     * Get attendance by class type for chart
     */
    public function getAttendanceByClass(Host $host, Carbon $start, Carbon $end): array
    {
        $classes = ClassSession::where('host_id', $host->id)
            ->whereBetween('start_time', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->with('classPlan')
            ->withCount([
                'bookings as completed_count' => function ($q) {
                    $q->where('status', Booking::STATUS_COMPLETED);
                },
                'bookings as no_show_count' => function ($q) {
                    $q->where('status', Booking::STATUS_NO_SHOW);
                },
                'bookings as cancelled_count' => function ($q) {
                    $q->where('status', Booking::STATUS_CANCELLED);
                },
            ])
            ->get()
            ->groupBy(fn($s) => $s->classPlan?->name ?? 'Other');

        $result = [];
        foreach ($classes as $className => $sessions) {
            $result[] = [
                'name' => $className,
                'completed' => $sessions->sum('completed_count'),
                'no_show' => $sessions->sum('no_show_count'),
                'cancelled' => $sessions->sum('cancelled_count'),
                'sessions' => $sessions->count(),
            ];
        }

        // Sort by completed desc
        usort($result, fn($a, $b) => $b['completed'] - $a['completed']);

        return array_slice($result, 0, 10); // Top 10 classes
    }

    /**
     * Get attendance by instructor for chart
     */
    public function getAttendanceByInstructor(Host $host, Carbon $start, Carbon $end): array
    {
        $sessions = ClassSession::where('host_id', $host->id)
            ->whereBetween('start_time', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', 'completed')
            ->with('primaryInstructor')
            ->withCount([
                'bookings as completed_count' => function ($q) {
                    $q->where('status', Booking::STATUS_COMPLETED);
                },
                'bookings as no_show_count' => function ($q) {
                    $q->where('status', Booking::STATUS_NO_SHOW);
                },
            ])
            ->get()
            ->groupBy(fn($s) => $s->primaryInstructor?->name ?? 'TBA');

        $result = [];
        foreach ($sessions as $instructorName => $instructorSessions) {
            $completed = $instructorSessions->sum('completed_count');
            $noShow = $instructorSessions->sum('no_show_count');
            $total = $completed + $noShow;
            $result[] = [
                'name' => $instructorName,
                'completed' => $completed,
                'no_show' => $noShow,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                'sessions' => $instructorSessions->count(),
            ];
        }

        // Sort by completed desc
        usort($result, fn($a, $b) => $b['completed'] - $a['completed']);

        return $result;
    }

    /**
     * Get attendance by day of week
     */
    public function getAttendanceByDayOfWeek(Host $host, Carbon $start, Carbon $end): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $result = [];

        foreach ($days as $index => $day) {
            $completed = Booking::where('host_id', $host->id)
                ->where('status', Booking::STATUS_COMPLETED)
                ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereRaw('DAYOFWEEK(booked_at) = ?', [$index + 1])
                ->count();

            $noShow = Booking::where('host_id', $host->id)
                ->where('status', Booking::STATUS_NO_SHOW)
                ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereRaw('DAYOFWEEK(booked_at) = ?', [$index + 1])
                ->count();

            $result[] = [
                'day' => $day,
                'completed' => $completed,
                'no_show' => $noShow,
            ];
        }

        return $result;
    }

    /**
     * Get attendance by hour of day
     */
    public function getAttendanceByHour(Host $host, Carbon $start, Carbon $end): array
    {
        $result = [];

        for ($hour = 6; $hour <= 21; $hour++) {
            $completed = Booking::where('host_id', $host->id)
                ->where('bookable_type', ClassSession::class)
                ->where('status', Booking::STATUS_COMPLETED)
                ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($hour) {
                    $q->whereRaw('HOUR(start_time) = ?', [$hour]);
                })
                ->count();

            $noShow = Booking::where('host_id', $host->id)
                ->where('bookable_type', ClassSession::class)
                ->where('status', Booking::STATUS_NO_SHOW)
                ->whereBetween('booked_at', [$start->startOfDay(), $end->endOfDay()])
                ->whereHasMorph('bookable', [ClassSession::class], function ($q) use ($hour) {
                    $q->whereRaw('HOUR(start_time) = ?', [$hour]);
                })
                ->count();

            $result[] = [
                'hour' => Carbon::createFromTime($hour)->format('g A'),
                'completed' => $completed,
                'no_show' => $noShow,
            ];
        }

        return $result;
    }

    /**
     * Get upcoming classes count
     */
    public function getUpcomingClassesCount(Host $host): int
    {
        return ClassSession::where('host_id', $host->id)
            ->where('start_time', '>', Carbon::now())
            ->where('status', 'published')
            ->count();
    }

    /**
     * Get classes count for today
     */
    public function getTodaysClassesCount(Host $host): int
    {
        return ClassSession::where('host_id', $host->id)
            ->whereDate('start_time', Carbon::today())
            ->whereIn('status', ['published', 'completed'])
            ->count();
    }
}
