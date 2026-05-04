<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class FetchTaxonImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly string $modelClass,
        public readonly int    $recordId,
        public readonly int    $max = 1,
    ) {
        $this->onQueue('species-images');
    }

    public function handle(): void
    {
        $model = \App\Models\Species::class === $this->modelClass ? 'species' : 'subspecies';

        Artisan::call('species:fetch-images', [
            '--model' => $model,
            '--id'    => $this->recordId,
            '--max'   => $this->max,
            '--delay' => 0,
        ]);
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }
}
