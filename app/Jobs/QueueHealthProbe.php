<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class QueueHealthProbe implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 10;

    public int $tries = 1;

    public function __construct(public string $key) {}

    public function handle(): void
    {
        Redis::connection('default')->setex($this->key, 120, (string) config('app.deployment_revision'));
    }
}
