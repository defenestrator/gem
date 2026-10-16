<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SyncAnimalsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public int $uniqueFor = 7200;

    public function __construct()
    {
        $this->onConnection('redis-imports')->onQueue('imports');
    }

    public function handle(): void
    {
        if (Artisan::call('animals:sync') !== 0) {
            throw new \RuntimeException('Animal import failed; see the command output and application logs.');
        }
    }
}
