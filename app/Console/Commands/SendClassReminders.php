<?php

namespace App\Console\Commands;

use App\Mail\ClassReminderMail;
use App\Models\AutomationSetting;
use App\Models\Booking;
use App\Models\ClassSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendClassReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automation:class-reminders {--dry-run : Run without actually sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to clients before their scheduled class (per-studio settings)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get all hosts that have class reminders enabled
        $enabledHostIds = AutomationSetting::getEnabledHostIds(AutomationSetting::KEY_CLASS_REMINDER);

        if (empty($enabledHostIds)) {
            $this->info('No studios have class reminders enabled.');
            return Command::SUCCESS;
        }

        $this->info('Processing class reminders for ' . count($enabledHostIds) . ' studio(s)...');

        $totalSent = 0;
        $totalFailed = 0;

        foreach ($enabledHostIds as $hostId) {
            $setting = AutomationSetting::getForHost($hostId, AutomationSetting::KEY_CLASS_REMINDER);

            if (!$setting) {
                continue;
            }

            $hoursBefore = $setting->getConfigValue('hours_before', 24);
            $targetTime = now()->addHours($hoursBefore);

            $this->line("Host #{$hostId}: Looking for classes starting around {$targetTime->format('Y-m-d H:i')} ({$hoursBefore}h before)...");

            // Get bookings for this host's classes starting in ~X hours (with 30-minute window)
            $bookings = Booking::with(['client', 'bookable.classPlan', 'host'])
                ->where('host_id', $hostId)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->whereNull('reminder_sent_at')
                ->whereHasMorph('bookable', [ClassSession::class], function ($query) use ($targetTime) {
                    $query->whereBetween('start_time', [
                        $targetTime->copy()->subMinutes(30),
                        $targetTime->copy()->addMinutes(30),
                    ]);
                })
                ->get();

            if ($bookings->isEmpty()) {
                $this->line("  No bookings found requiring reminders.");
                continue;
            }

            $this->info("  Found {$bookings->count()} booking(s) to send reminders for.");

            $sent = 0;
            $failed = 0;

            foreach ($bookings as $booking) {
                if (!$booking->client?->email) {
                    $this->warn("  Skipping booking #{$booking->id}: No client email");
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->info("  [DRY RUN] Would send reminder to: {$booking->client->email}");
                    $sent++;
                    continue;
                }

                try {
                    Mail::to($booking->client->email)->queue(new ClassReminderMail($booking));
                    $booking->update(['reminder_sent_at' => now()]);
                    $sent++;

                    $this->info("  Sent reminder to: {$booking->client->email}");
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("  Failed to send to {$booking->client->email}: {$e->getMessage()}");
                }
            }

            // Update last run time for this host's setting
            $setting->update(['last_run_at' => now()]);

            $totalSent += $sent;
            $totalFailed += $failed;

            $this->line("  Host #{$hostId}: {$sent} sent, {$failed} failed.");
        }

        $this->info("Complete: {$totalSent} total sent, {$totalFailed} total failed across " . count($enabledHostIds) . " studio(s).");

        return Command::SUCCESS;
    }
}
