<?php

namespace App\Services\Reporting;

use App\Models\Host;
use App\Services\Reporting\Metrics\RevenueMetrics;
use App\Services\Reporting\Metrics\MembershipMetrics;
use App\Services\Reporting\Metrics\AttendanceMetrics;
use Carbon\Carbon;

class ReportingService
{
    protected RevenueMetrics $revenueMetrics;
    protected MembershipMetrics $membershipMetrics;
    protected AttendanceMetrics $attendanceMetrics;

    public function __construct(
        RevenueMetrics $revenueMetrics,
        MembershipMetrics $membershipMetrics,
        AttendanceMetrics $attendanceMetrics
    ) {
        $this->revenueMetrics = $revenueMetrics;
        $this->membershipMetrics = $membershipMetrics;
        $this->attendanceMetrics = $attendanceMetrics;
    }

    /**
     * Get all dashboard metrics in one call
     */
    public function getDashboardMetrics(Host $host): array
    {
        return [
            'revenue' => $this->revenueMetrics->getSummary($host),
            'members' => $this->membershipMetrics->getSummary($host),
            'attendance' => $this->attendanceMetrics->getSummary($host),
            'todays_classes' => $this->attendanceMetrics->getTodaysClasses($host),
            'top_class' => $this->attendanceMetrics->getTopPerformingClass($host),
            'outstanding_invoices' => $this->revenueMetrics->getOutstandingInvoices($host),
        ];
    }

    /**
     * Get revenue-specific metrics
     */
    public function getRevenueReport(Host $host, Carbon $start, Carbon $end): array
    {
        return [
            'summary' => $this->revenueMetrics->getRevenueForPeriod($host, $start, $end),
            'by_payment_method' => $this->revenueMetrics->getByPaymentMethod($host, $start, $end),
            'by_type' => $this->revenueMetrics->getByType($host, $start, $end),
            'daily' => $this->revenueMetrics->getDailyRevenue($host, $start),
            'outstanding' => $this->revenueMetrics->getOutstandingInvoices($host),
        ];
    }

    /**
     * Get membership-specific metrics
     */
    public function getMembershipReport(Host $host): array
    {
        return [
            'summary' => $this->membershipMetrics->getSummary($host),
            'by_plan' => $this->membershipMetrics->getByPlanType($host),
            'status_breakdown' => $this->membershipMetrics->getStatusBreakdown($host),
            'growth' => $this->membershipMetrics->getGrowthMetrics($host),
            'arpm' => $this->membershipMetrics->calculateARPM($host),
        ];
    }

    /**
     * Get attendance-specific metrics
     */
    public function getAttendanceReport(Host $host, Carbon $start, Carbon $end): array
    {
        return [
            'summary' => $this->attendanceMetrics->getSummary($host),
            'status_breakdown' => $this->attendanceMetrics->getBookingStatusBreakdown($host, $start, $end),
            'source_breakdown' => $this->attendanceMetrics->getBookingSourceBreakdown($host, $start, $end),
            'todays_classes' => $this->attendanceMetrics->getTodaysClasses($host),
        ];
    }

    /**
     * Get chart data for revenue trends
     */
    public function getRevenueChartData(Host $host, string $period = 'month'): array
    {
        return $this->revenueMetrics->getChartData($host, $period);
    }

    /**
     * Get chart data for membership trends
     */
    public function getMembershipChartData(Host $host, int $months = 12): array
    {
        return $this->membershipMetrics->getChartData($host, $months);
    }

    /**
     * Get chart data for attendance trends
     */
    public function getAttendanceChartData(Host $host, int $weeks = 12): array
    {
        return $this->attendanceMetrics->getChartData($host, $weeks);
    }

    /**
     * Get quick stats for the dashboard header
     */
    public function getQuickStats(Host $host): array
    {
        $today = Carbon::today();
        $revenue = $this->revenueMetrics->getSummary($host);
        $members = $this->membershipMetrics->getSummary($host);
        $attendance = $this->attendanceMetrics->getSummary($host);

        return [
            'revenue_today' => $revenue['today']['gross'],
            'revenue_mtd' => $revenue['mtd']['gross'],
            'revenue_ytd' => $revenue['ytd']['gross'],
            'active_members' => $members['active'],
            'new_members_30d' => $members['new_30_days'],
            'mrr' => $members['mrr'],
            'attendance_rate' => $attendance['attendance_rate'],
            'classes_today' => $this->attendanceMetrics->getTodaysClassesCount($host),
            'upcoming_classes' => $this->attendanceMetrics->getUpcomingClassesCount($host),
        ];
    }

    /**
     * Get individual metrics services for advanced usage
     */
    public function revenue(): RevenueMetrics
    {
        return $this->revenueMetrics;
    }

    public function membership(): MembershipMetrics
    {
        return $this->membershipMetrics;
    }

    public function attendance(): AttendanceMetrics
    {
        return $this->attendanceMetrics;
    }
}
