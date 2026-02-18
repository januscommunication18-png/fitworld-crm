<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Host;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetectAtRiskClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:detect-at-risk
                            {--days=30 : Number of days of inactivity to consider at-risk}
                            {--membership-days=14 : Days before membership expiry to consider at-risk}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and mark clients/members as at-risk based on inactivity or expiring memberships';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inactivityDays = (int) $this->option('days');
        $membershipDays = (int) $this->option('membership-days');
        $dryRun = $this->option('dry-run');

        $this->info("Detecting at-risk clients...");
        $this->info("  - Inactivity threshold: {$inactivityDays} days");
        $this->info("  - Membership expiry threshold: {$membershipDays} days");

        if ($dryRun) {
            $this->warn("  - DRY RUN MODE: No changes will be made");
        }

        $totalMarked = 0;

        // Process each host separately
        Host::where('status', Host::STATUS_ACTIVE)->chunk(100, function ($hosts) use ($inactivityDays, $membershipDays, $dryRun, &$totalMarked) {
            foreach ($hosts as $host) {
                $marked = $this->processHost($host, $inactivityDays, $membershipDays, $dryRun);
                $totalMarked += $marked;
            }
        });

        $this->newLine();
        $this->info("Total clients marked as at-risk: {$totalMarked}");

        return Command::SUCCESS;
    }

    /**
     * Process a single host's clients
     */
    protected function processHost(Host $host, int $inactivityDays, int $membershipDays, bool $dryRun): int
    {
        $markedCount = 0;
        $inactivityDate = now()->subDays($inactivityDays);
        $membershipExpiryDate = now()->addDays($membershipDays);

        // Find clients/members who should be marked as at-risk
        $atRiskClients = Client::where('host_id', $host->id)
            ->whereNull('archived_at')
            ->whereIn('status', [Client::STATUS_CLIENT, Client::STATUS_MEMBER])
            ->where(function ($query) use ($inactivityDate, $membershipExpiryDate) {
                // Condition 1: No activity in X days (last_visit_at is null or old)
                $query->where(function ($q) use ($inactivityDate) {
                    $q->whereNull('last_visit_at')
                      ->orWhere('last_visit_at', '<', $inactivityDate);
                })
                // Condition 2: OR membership expiring soon
                ->orWhere(function ($q) use ($membershipExpiryDate) {
                    $q->where('membership_status', Client::MEMBERSHIP_ACTIVE)
                      ->whereNotNull('membership_expires_at')
                      ->where('membership_expires_at', '<=', $membershipExpiryDate)
                      ->where('membership_expires_at', '>', now());
                });
            })
            ->get();

        foreach ($atRiskClients as $client) {
            $reason = $this->getAtRiskReason($client, $inactivityDate, $membershipExpiryDate);

            if ($dryRun) {
                $this->line("  [DRY RUN] Would mark: {$client->full_name} ({$client->email}) - {$reason}");
            } else {
                $client->update(['status' => Client::STATUS_AT_RISK]);
                $this->line("  Marked: {$client->full_name} ({$client->email}) - {$reason}");
            }

            $markedCount++;
        }

        if ($markedCount > 0) {
            $this->info("  Host '{$host->studio_name}': {$markedCount} clients marked as at-risk");
        }

        return $markedCount;
    }

    /**
     * Get the reason why a client is at-risk
     */
    protected function getAtRiskReason(Client $client, $inactivityDate, $membershipExpiryDate): string
    {
        $reasons = [];

        if (!$client->last_visit_at || $client->last_visit_at < $inactivityDate) {
            $daysInactive = $client->last_visit_at
                ? now()->diffInDays($client->last_visit_at)
                : 'never visited';
            $reasons[] = "inactive ({$daysInactive} days)";
        }

        if ($client->membership_status === Client::MEMBERSHIP_ACTIVE
            && $client->membership_expires_at
            && $client->membership_expires_at <= $membershipExpiryDate
            && $client->membership_expires_at > now()) {
            $daysUntilExpiry = now()->diffInDays($client->membership_expires_at);
            $reasons[] = "membership expiring in {$daysUntilExpiry} days";
        }

        return implode(', ', $reasons);
    }
}
