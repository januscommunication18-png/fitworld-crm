<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Host;
use App\Models\UserSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ArchiveAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:archive {--days=90 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive audit logs and user sessions older than 90 days to CSV and email to admin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Archiving logs older than {$days} days (before {$cutoffDate->format('Y-m-d')})...");

        $hosts = Host::all();

        foreach ($hosts as $host) {
            $this->archiveHostLogs($host, $cutoffDate);
        }

        $this->info('Audit log archival complete.');

        return Command::SUCCESS;
    }

    /**
     * Archive logs for a specific host
     */
    protected function archiveHostLogs(Host $host, $cutoffDate): void
    {
        // Get old audit logs
        $auditLogs = AuditLog::where('host_id', $host->id)
            ->where('created_at', '<', $cutoffDate)
            ->with('user')
            ->get();

        // Get old user sessions
        $userSessions = UserSession::where('host_id', $host->id)
            ->where('created_at', '<', $cutoffDate)
            ->with('user')
            ->get();

        if ($auditLogs->isEmpty() && $userSessions->isEmpty()) {
            $this->line("No logs to archive for {$host->name}");
            return;
        }

        $this->line("Archiving {$auditLogs->count()} audit logs and {$userSessions->count()} sessions for {$host->name}...");

        $archiveDir = 'audit-archives/' . $host->id;
        $timestamp = now()->format('Y-m-d_His');

        $files = [];

        // Export audit logs to CSV
        if ($auditLogs->isNotEmpty()) {
            $auditCsvPath = "{$archiveDir}/audit_logs_{$timestamp}.csv";
            $this->exportAuditLogsToCsv($auditLogs, $auditCsvPath);
            $files[] = Storage::path($auditCsvPath);
        }

        // Export user sessions to CSV
        if ($userSessions->isNotEmpty()) {
            $sessionsCsvPath = "{$archiveDir}/user_sessions_{$timestamp}.csv";
            $this->exportSessionsToCsv($userSessions, $sessionsCsvPath);
            $files[] = Storage::path($sessionsCsvPath);
        }

        // Email the archive to the studio owner
        $owner = $host->users()->where('role', 'owner')->first();
        if ($owner && !empty($files)) {
            $this->sendArchiveEmail($host, $owner, $files, $cutoffDate);
        }

        // Delete archived records
        AuditLog::where('host_id', $host->id)
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        UserSession::where('host_id', $host->id)
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Archived and deleted logs for {$host->name}");
    }

    /**
     * Export audit logs to CSV
     */
    protected function exportAuditLogsToCsv($logs, string $path): void
    {
        $headers = [
            'ID',
            'Date',
            'User',
            'Action',
            'Entity Type',
            'Entity ID',
            'Reason',
            'IP Address',
            'User Agent',
            'Before Data',
            'After Data',
        ];

        $rows = $logs->map(fn($log) => [
            $log->id,
            $log->created_at->format('Y-m-d H:i:s'),
            $log->user?->name ?? 'System',
            $log->action_label,
            $log->auditable_type ? class_basename($log->auditable_type) : '',
            $log->auditable_id ?? '',
            $log->reason ?? '',
            $log->ip_address ?? '',
            $log->user_agent ?? '',
            $log->before_data ? json_encode($log->before_data) : '',
            $log->after_data ? json_encode($log->after_data) : '',
        ]);

        $this->writeCsv($path, $headers, $rows);
    }

    /**
     * Export user sessions to CSV
     */
    protected function exportSessionsToCsv($sessions, string $path): void
    {
        $headers = [
            'ID',
            'User',
            'Login Time',
            'Logout Time',
            'Duration',
            'IP Address',
            'Browser',
            'Platform',
            'Device Type',
            'Location',
        ];

        $rows = $sessions->map(fn($session) => [
            $session->id,
            $session->user?->name ?? 'Unknown',
            $session->logged_in_at?->format('Y-m-d H:i:s') ?? '',
            $session->logged_out_at?->format('Y-m-d H:i:s') ?? '',
            $session->session_duration ?? '',
            $session->ip_address ?? '',
            ($session->browser ?? '') . ' ' . ($session->browser_version ?? ''),
            $session->platform ?? '',
            $session->device_type ?? '',
            $session->location ?? '',
        ]);

        $this->writeCsv($path, $headers, $rows);
    }

    /**
     * Write data to CSV file
     */
    protected function writeCsv(string $path, array $headers, $rows): void
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row->toArray());
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::put($path, $content);
    }

    /**
     * Send archive email to studio owner
     */
    protected function sendArchiveEmail(Host $host, $owner, array $files, $cutoffDate): void
    {
        try {
            Mail::send([], [], function ($message) use ($host, $owner, $files, $cutoffDate) {
                $message->to($owner->email)
                    ->subject("Audit Log Archive - {$host->name}")
                    ->html($this->getEmailContent($host, $cutoffDate));

                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $message->attach($file);
                    }
                }
            });

            $this->line("Archive email sent to {$owner->email}");
        } catch (\Exception $e) {
            $this->error("Failed to send archive email: {$e->getMessage()}");
        }
    }

    /**
     * Get email HTML content
     */
    protected function getEmailContent(Host $host, $cutoffDate): string
    {
        return <<<HTML
        <h2>Audit Log Archive</h2>
        <p>The attached CSV files contain archived audit logs and user session records for <strong>{$host->name}</strong>.</p>
        <p>These records are older than {$cutoffDate->format('F j, Y')} and have been removed from the system for data retention compliance.</p>
        <p>Please store these files securely for your records.</p>
        <br>
        <p style="color: #666; font-size: 12px;">This is an automated message from FitCRM.</p>
        HTML;
    }
}
