<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Host;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with(['host', 'template']);

        // Filter by host
        if ($request->filled('host_id')) {
            $query->where('host_id', $request->host_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        $hosts = Host::orderBy('studio_name')->get(['id', 'studio_name']);

        // Stats
        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'delivered' => EmailLog::where('status', 'delivered')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
        ];

        return view('backoffice.email-logs.index', compact('logs', 'hosts', 'stats'));
    }

    public function show(EmailLog $emailLog)
    {
        $emailLog->load(['host', 'template']);

        return view('backoffice.email-logs.show', compact('emailLog'));
    }

    public function destroy(EmailLog $emailLog)
    {
        $emailLog->delete();

        return redirect()->route('backoffice.email-logs.index')
            ->with('success', 'Email log deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:email_logs,id',
        ]);

        EmailLog::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('backoffice.email-logs.index')
            ->with('success', count($validated['ids']) . ' email log(s) deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = EmailLog::with(['host', 'template']);

        // Apply same filters as index
        if ($request->filled('host_id')) {
            $query->where('host_id', $request->host_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'email-logs-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'ID',
                'Client',
                'Recipient Email',
                'Recipient Name',
                'Subject',
                'Status',
                'Provider',
                'Sent At',
                'Created At',
            ]);

            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->host?->studio_name ?? 'System',
                    $log->recipient_email,
                    $log->recipient_name,
                    $log->subject,
                    $log->status,
                    $log->provider,
                    $log->sent_at?->format('Y-m-d H:i:s'),
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
