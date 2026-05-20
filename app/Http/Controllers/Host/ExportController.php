<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    private function authorizeExport(string $permission, string $message): void
    {
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, $message);
        }
    }

    /**
     * Export clients to CSV
     */
    public function clients(Request $request)
    {
        $this->authorizeExport('students.export', 'You do not have permission to export clients.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportClients($host, $request->all());
    }

    /**
     * Export transactions to CSV
     */
    public function transactions(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportTransactions($host, [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ]);
    }

    /**
     * Export bookings to CSV
     */
    public function bookings(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportBookings($host, [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ]);
    }

    /**
     * Export class sessions to CSV
     */
    public function classes(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportClassSessions($host, [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ]);
    }

    /**
     * Export memberships to CSV
     */
    public function memberships(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportMemberships($host, $request->all());
    }

    /**
     * Export instructors to CSV
     */
    public function instructors(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportInstructors($host, $request->all());
    }

    /**
     * Export audit logs to CSV
     */
    public function auditLogs(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportAuditLogs($host, [
            'period' => $request->get('period', 90),
            'user' => $request->get('user'),
            'category' => $request->get('category'),
        ]);
    }

    /**
     * Export user sessions to CSV
     */
    public function userSessions(Request $request)
    {
        $this->authorizeExport('insights.export', 'You do not have permission to export reports.');
        $host = auth()->user()->currentHost();

        return $this->exportService->exportUserSessions($host, [
            'period' => $request->get('period', 90),
            'user' => $request->get('user'),
        ]);
    }
}
