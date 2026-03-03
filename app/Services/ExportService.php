<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\Transaction;
use App\Models\UserSession;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export clients to CSV
     */
    public function exportClients(Host $host, array $filters = []): StreamedResponse
    {
        $clients = Client::where('host_id', $host->id)
            ->with(['activeMembership'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Status',
            'Membership Status', 'Join Date', 'Last Visit', 'Total Classes',
            'Total Spent', 'Lead Source', 'City', 'Country',
        ];

        return $this->streamCsv('clients', $headers, $clients, function ($client) {
            return [
                $client->id,
                $client->first_name,
                $client->last_name,
                $client->email,
                $client->phone ?? '',
                $client->status,
                $client->membership_status,
                $client->created_at->format('Y-m-d'),
                $client->last_visit_at?->format('Y-m-d') ?? '',
                $client->total_classes_attended,
                number_format($client->total_spent, 2),
                $client->lead_source ?? '',
                $client->city ?? '',
                $client->country ?? '',
            ];
        });
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(Host $host, array $filters = []): StreamedResponse
    {
        $query = Transaction::where('host_id', $host->id)
            ->with(['client'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $transactions = $query->get();

        $headers = [
            'ID', 'Date', 'Client', 'Email', 'Type', 'Description',
            'Amount', 'Tax', 'Total', 'Payment Method', 'Status', 'Paid At',
        ];

        return $this->streamCsv('transactions', $headers, $transactions, function ($txn) {
            return [
                $txn->id,
                $txn->created_at->format('Y-m-d H:i:s'),
                $txn->client?->first_name . ' ' . $txn->client?->last_name,
                $txn->client?->email ?? '',
                $txn->type,
                $txn->description ?? '',
                number_format($txn->amount, 2),
                number_format($txn->tax_amount ?? 0, 2),
                number_format(($txn->amount ?? 0) + ($txn->tax_amount ?? 0), 2),
                $txn->payment_method ?? $txn->manual_method ?? '',
                $txn->status,
                $txn->paid_at?->format('Y-m-d H:i:s') ?? '',
            ];
        });
    }

    /**
     * Export bookings to CSV
     */
    public function exportBookings(Host $host, array $filters = []): StreamedResponse
    {
        $query = Booking::where('host_id', $host->id)
            ->with(['client', 'bookable'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $bookings = $query->get();

        $headers = [
            'ID', 'Booked At', 'Client', 'Email', 'Type', 'Item',
            'Date/Time', 'Status', 'Payment Method', 'Price Paid', 'Checked In At',
        ];

        return $this->streamCsv('bookings', $headers, $bookings, function ($booking) {
            $bookableType = class_basename($booking->bookable_type ?? '');
            $bookableName = '';
            $bookableTime = '';

            if ($booking->bookable) {
                if ($booking->bookable instanceof ClassSession) {
                    $bookableName = $booking->bookable->classPlan?->name ?? 'Class';
                    $bookableTime = $booking->bookable->start_time?->format('Y-m-d H:i') ?? '';
                } else {
                    $bookableName = $booking->bookable->name ?? $booking->bookable->title ?? 'Item';
                }
            }

            return [
                $booking->id,
                $booking->booked_at?->format('Y-m-d H:i:s') ?? $booking->created_at->format('Y-m-d H:i:s'),
                $booking->client?->first_name . ' ' . $booking->client?->last_name,
                $booking->client?->email ?? '',
                $bookableType,
                $bookableName,
                $bookableTime,
                $booking->status,
                $booking->payment_method ?? '',
                number_format($booking->price_paid ?? 0, 2),
                $booking->checked_in_at?->format('Y-m-d H:i:s') ?? '',
            ];
        });
    }

    /**
     * Export class sessions to CSV
     */
    public function exportClassSessions(Host $host, array $filters = []): StreamedResponse
    {
        $query = ClassSession::where('host_id', $host->id)
            ->with(['classPlan', 'instructor', 'bookings'])
            ->orderBy('start_time', 'desc');

        if (!empty($filters['from_date'])) {
            $query->whereDate('start_time', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('start_time', '<=', $filters['to_date']);
        }

        $sessions = $query->get();

        $headers = [
            'ID', 'Date', 'Start Time', 'End Time', 'Class Name',
            'Instructor', 'Location', 'Capacity', 'Booked', 'Attended',
            'Revenue', 'Status',
        ];

        return $this->streamCsv('class_sessions', $headers, $sessions, function ($session) {
            $attended = $session->bookings->where('status', 'completed')->count();

            return [
                $session->id,
                $session->start_time?->format('Y-m-d'),
                $session->start_time?->format('H:i'),
                $session->end_time?->format('H:i'),
                $session->classPlan?->name ?? 'Unknown',
                $session->instructor?->name ?? '',
                $session->location ?? '',
                $session->capacity ?? '',
                $session->bookings->count(),
                $attended,
                number_format($session->bookings->sum('price_paid') ?? 0, 2),
                $session->status ?? 'scheduled',
            ];
        });
    }

    /**
     * Export memberships to CSV
     */
    public function exportMemberships(Host $host, array $filters = []): StreamedResponse
    {
        $memberships = CustomerMembership::where('host_id', $host->id)
            ->with(['client', 'membershipPlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'ID', 'Client', 'Email', 'Membership Plan', 'Status',
            'Start Date', 'End Date', 'Price', 'Billing Interval',
            'Next Billing Date', 'Created At',
        ];

        return $this->streamCsv('memberships', $headers, $memberships, function ($membership) {
            return [
                $membership->id,
                $membership->client?->first_name . ' ' . $membership->client?->last_name,
                $membership->client?->email ?? '',
                $membership->membershipPlan?->name ?? 'Unknown',
                $membership->status,
                $membership->start_date?->format('Y-m-d') ?? '',
                $membership->end_date?->format('Y-m-d') ?? '',
                number_format($membership->price ?? 0, 2),
                $membership->billing_interval ?? '',
                $membership->next_billing_date?->format('Y-m-d') ?? '',
                $membership->created_at->format('Y-m-d'),
            ];
        });
    }

    /**
     * Export instructors to CSV
     */
    public function exportInstructors(Host $host, array $filters = []): StreamedResponse
    {
        $instructors = Instructor::where('host_id', $host->id)
            ->withCount(['classSessions', 'serviceSessions'])
            ->orderBy('name')
            ->get();

        $headers = [
            'ID', 'Name', 'Email', 'Phone', 'Status',
            'Total Classes', 'Total Services', 'Created At',
        ];

        return $this->streamCsv('instructors', $headers, $instructors, function ($instructor) {
            return [
                $instructor->id,
                $instructor->name,
                $instructor->email ?? '',
                $instructor->phone ?? '',
                $instructor->status ?? 'active',
                $instructor->class_sessions_count ?? 0,
                $instructor->service_sessions_count ?? 0,
                $instructor->created_at->format('Y-m-d'),
            ];
        });
    }

    /**
     * Export audit logs to CSV
     */
    public function exportAuditLogs(Host $host, array $filters = []): StreamedResponse
    {
        $query = AuditLog::where('host_id', $host->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if (!empty($filters['period'])) {
            $query->where('created_at', '>=', now()->subDays((int) $filters['period']));
        }
        if (!empty($filters['user'])) {
            $query->where('user_id', $filters['user']);
        }
        if (!empty($filters['category'])) {
            $query->where('action', 'like', $filters['category'] . '.%');
        }

        $logs = $query->get();

        $headers = [
            'ID', 'Date', 'Time', 'User', 'Action', 'Entity Type',
            'Entity ID', 'Reason', 'IP Address', 'Details',
        ];

        return $this->streamCsv('audit_logs', $headers, $logs, function ($log) {
            return [
                $log->id,
                $log->created_at->format('Y-m-d'),
                $log->created_at->format('H:i:s'),
                $log->user?->name ?? 'System',
                $log->action_label,
                $log->auditable_type ? class_basename($log->auditable_type) : '',
                $log->auditable_id ?? '',
                $log->reason ?? '',
                $log->ip_address ?? '',
                $log->context ? json_encode($log->context) : '',
            ];
        });
    }

    /**
     * Export user sessions to CSV
     */
    public function exportUserSessions(Host $host, array $filters = []): StreamedResponse
    {
        $query = UserSession::where('host_id', $host->id)
            ->with('user')
            ->orderBy('logged_in_at', 'desc');

        if (!empty($filters['period'])) {
            $query->where('created_at', '>=', now()->subDays((int) $filters['period']));
        }
        if (!empty($filters['user'])) {
            $query->where('user_id', $filters['user']);
        }

        $sessions = $query->get();

        $headers = [
            'ID', 'User', 'Login Time', 'Logout Time', 'Duration',
            'IP Address', 'Browser', 'Platform', 'Device', 'Location', 'Status',
        ];

        return $this->streamCsv('user_sessions', $headers, $sessions, function ($session) {
            return [
                $session->id,
                $session->user?->name ?? 'Unknown',
                $session->logged_in_at?->format('Y-m-d H:i:s'),
                $session->logged_out_at?->format('Y-m-d H:i:s') ?? '',
                $session->session_duration ?? '',
                $session->ip_address ?? '',
                ($session->browser ?? '') . ' ' . ($session->browser_version ?? ''),
                $session->platform ?? '',
                $session->device_type ?? '',
                $session->location ?? '',
                $session->is_active ? 'Active' : 'Ended',
            ];
        });
    }

    /**
     * Stream CSV response
     */
    protected function streamCsv(string $name, array $headers, $data, callable $mapper): StreamedResponse
    {
        $filename = "{$name}_" . now()->format('Y-m-d_His') . '.csv';

        return Response::stream(function () use ($headers, $data, $mapper) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($data as $row) {
                fputcsv($handle, $mapper($row));
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
