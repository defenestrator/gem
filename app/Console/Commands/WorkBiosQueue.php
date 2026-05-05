<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WorkBiosQueue extends Command
{
    protected $signature = 'species:work-bios
        {--once    : Process a single job then exit}
        {--tries=3 : Number of times to attempt a job before marking it failed}
        {--timeout=120 : Seconds a job may run before it is killed}';

    protected $description = 'Start a queue worker for the species-bios queue';

    public function handle(): int
    {
        $args = [
            'connection' => config('queue.default'),
            '--queue'    => 'species-bios',
            '--sleep'    => 3,
            '--tries'    => (int) $this->option('tries'),
            '--timeout'  => (int) $this->option('timeout'),
        ];

        if ($this->option('once')) {
            $args['--once'] = true;
        }

        return $this->call('queue:work', $args);
    }
}
