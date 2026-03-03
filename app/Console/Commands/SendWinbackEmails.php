<?php

namespace App\Console\Commands;

use App\Mail\WinbackMail;
use App\Models\AutomationSetting;
use App\Models\Client;
use App\Models\Host;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWinbackEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automation:winback {--dry-run : Run without actually sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send win-back emails to inactive members (per-studio settings)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get all hosts that have win-back campaign enabled
        $enabledHostIds = AutomationSetting::getEnabledHostIds(AutomationSetting::KEY_WINBACK_CAMPAIGN);

        if (empty($enabledHostIds)) {
            $this->info('No studios have win-back campaign enabled.');
            return Command::SUCCESS;
        }

        $this->info('Processing win-back campaigns for ' . count($enabledHostIds) . ' studio(s)...');

        $totalSent = 0;
        $totalFailed = 0;

        foreach ($enabledHostIds as $hostId) {
            $setting = AutomationSetting::getForHost($hostId, AutomationSetting::KEY_WINBACK_CAMPAIGN);
            $host = Host::find($hostId);

            if (!$setting || !$host) {
                continue;
            }

            $daysInactive = $setting->getConfigValue('days_inactive', 60);
            $resendAfterDays = $setting->getConfigValue('resend_after_days', 30);

            $this->line("Host '{$host->studio_name}': Looking for clients inactive for {$daysInactive}+ days...");

            // Find clients with no bookings in X days
            $inactiveClients = Client::where('host_id', $hostId)
                ->where('status', '!=', 'lead') // Only active clients, not leads
                ->whereDoesntHave('bookings', function ($query) use ($daysInactive) {
                    $query->where('booked_at', '>=', now()->subDays($daysInactive));
                })
                ->where(function ($query) use ($resendAfterDays) {
                    $query->whereNull('winback_sent_at')
                        ->orWhere('winback_sent_at', '<', now()->subDays($resendAfterDays));
                })
                ->get();

            if ($inactiveClients->isEmpty()) {
                $this->line("  No inactive clients found.");
                continue;
            }

            $this->info("  Found {$inactiveClients->count()} inactive client(s).");

            $sent = 0;
            $failed = 0;

            foreach ($inactiveClients as $client) {
                if (!$client->email) {
                    $this->warn("  Skipping client #{$client->id}: No email");
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->info("  [DRY RUN] Would send win-back to: {$client->email}");
                    $sent++;
                    continue;
                }

                try {
                    Mail::to($client->email)->queue(new WinbackMail($client, $host));
                    $client->update(['winback_sent_at' => now()]);
                    $sent++;

                    $this->info("  Sent win-back to: {$client->email}");
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("  Failed to send to {$client->email}: {$e->getMessage()}");
                }
            }

            // Update last run time for this host's setting
            $setting->update(['last_run_at' => now()]);

            $totalSent += $sent;
            $totalFailed += $failed;

            $this->line("  Host '{$host->studio_name}': {$sent} sent, {$failed} failed.");
        }

        $this->info("Complete: {$totalSent} total sent, {$totalFailed} total failed across " . count($enabledHostIds) . " studio(s).");

        return Command::SUCCESS;
    }
}
