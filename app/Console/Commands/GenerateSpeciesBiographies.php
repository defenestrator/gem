<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSpeciesBiographyJob;
use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

/**
 * Dispatches GenerateSpeciesBiographyJob per record onto the species-bios queue.
 *
 * Phased usage:
 *   php artisan species:generate-bios --model=species --limit=500
 *   php artisan species:generate-bios --model=subspecies --limit=500
 *   php artisan species:generate-bios --id=42
 *   php artisan species:generate-bios --model=all --force --dry-run
 */
class GenerateSpeciesBiographies extends Command
{
    protected $signature = 'species:generate-bios
        {--model=species  : species | subspecies | all}
        {--limit=500      : Max records to dispatch per run}
        {--id=            : Process a single record by ID}
        {--force          : Re-generate even if description already exists}
        {--dry-run        : Preview without dispatching}';

    protected $description = 'Dispatch queued biography-generation jobs for species and subspecies';

    public function handle(): int
    {
        if (! config('services.anthropic.key')) {
            $this->error('ANTHROPIC_API_KEY is not set. Aborting.');
            return self::FAILURE;
        }

        $model  = $this->option('model');
        $limit  = (int) $this->option('limit');
        $id     = $this->option('id');
        $force  = (bool) $this->option('force');
        $dry    = (bool) $this->option('dry-run');

        if ($dry) {
            $this->info('[DRY RUN — no jobs will be dispatched]');
        }

        $total = 0;

        if ($id !== null) {
            $modelClass = $model === 'subspecies' ? Subspecies::class : Species::class;
            $this->dispatch($modelClass, (int) $id, $force, $dry);
            $total = 1;
        } else {
            if (in_array($model, ['species', 'all'])) {
                $total += $this->dispatchBatch(Species::class, $limit, $force, $dry);
            }
            if (in_array($model, ['subspecies', 'all'])) {
                $total += $this->dispatchBatch(Subspecies::class, $limit, $force, $dry);
            }
        }

        $verb = $dry ? 'Would dispatch' : 'Dispatched';
        $this->info("{$verb} {$total} job(s) → queue: species-bios");

        return self::SUCCESS;
    }

    private function dispatchBatch(string $modelClass, int $limit, bool $force, bool $dry): int
    {
        $label = class_basename($modelClass);
        $count = 0;

        $query = $modelClass::query();
        if (! $force) {
            $query->whereNull('description')->orWhere('description', '');
        }

        $query->orderBy('id')->limit($limit)->chunkById(100, function ($rows) use (&$count, $modelClass, $force, $dry, $label) {
            foreach ($rows as $row) {
                $this->dispatch($modelClass, $row->id, $force, $dry);
                $count++;
            }
            $this->line("  {$label}: queued {$count} so far…");
        });

        return $count;
    }

    private function dispatch(string $modelClass, int $id, bool $force, bool $dry): void
    {
        if ($dry) {
            $this->line("  [dry-run] {$modelClass}#{$id}");
            return;
        }
        GenerateSpeciesBiographyJob::dispatch($modelClass, $id, $force);
    }
}
