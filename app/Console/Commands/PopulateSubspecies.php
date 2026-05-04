<?php

namespace App\Console\Commands;

use App\Models\Subspecies;
use Illuminate\Console\Command;

class PopulateSubspecies extends Command
{
    protected $signature = 'species:populate-subspecies
                            {--force : Re-extract even if subspecies table is already populated}';

    protected $description = 'Populate the subspecies table from species data (safe to run on every deploy)';

    public function handle(): int
    {
        $existing = Subspecies::count();

        if ($existing > 0 && ! $this->option('force')) {
            $this->line("subspecies table already populated ({$existing} rows). Skipping.");
            return self::SUCCESS;
        }

        $flags = $this->option('force') ? ['--force' => true] : [];

        return $this->call('subspecies:extract', $flags);
    }
}
