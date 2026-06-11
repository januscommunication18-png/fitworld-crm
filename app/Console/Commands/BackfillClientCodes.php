<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

/**
 * Generate Client Token IDs for existing clients (codes are otherwise
 * created lazily). Idempotent — clients that already have a code are
 * skipped.
 */
class BackfillClientCodes extends Command
{
    protected $signature = 'clients:generate-codes {--host= : Only this host id}';

    protected $description = 'Backfill client_code for clients that do not have one yet';

    public function handle(): int
    {
        $query = Client::whereNull('client_code')
            ->when($this->option('host'), fn ($q, $hostId) => $q->where('host_id', $hostId));

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('All clients already have codes.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $generated = 0;

        $query->with('host')->chunkById(200, function ($clients) use ($bar, &$generated) {
            foreach ($clients as $client) {
                $client->getOrCreateClientCode();
                $generated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Generated {$generated} client codes.");

        return self::SUCCESS;
    }
}
