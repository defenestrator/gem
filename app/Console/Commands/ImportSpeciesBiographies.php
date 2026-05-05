<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

class ImportSpeciesBiographies extends Command
{
    protected $signature = 'species:import-bios
        {file            : Path to JSON export file}
        {--force         : Overwrite existing descriptions}
        {--dry-run       : Preview without writing}';

    protected $description = 'Idempotently import species/subspecies biographies from a JSON export file';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);

        if (! is_array($rows)) {
            $this->error('Invalid JSON.');
            return self::FAILURE;
        }

        $dry     = (bool) $this->option('dry-run');
        $force   = (bool) $this->option('force');
        $updated = 0;
        $skipped = 0;
        $missing = 0;

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        foreach ($rows as $row) {
            $type        = $row['type']        ?? null;
            $name        = $row['name']        ?? null;
            $description = $row['description'] ?? null;

            if (! $type || ! $name || ! $description) {
                continue;
            }

            if ($type === 'species') {
                $record = Species::where('species', $name)->first();
            } else {
                $parts  = explode(' ', $name, 3);
                $record = Subspecies::where('genus',      $parts[0] ?? '')
                    ->where('species',    $parts[1] ?? '')
                    ->where('subspecies', $parts[2] ?? '')
                    ->first();
            }

            if (! $record) {
                $this->warn("  Not found: {$name}");
                $missing++;
                continue;
            }

            if (! $force && ! empty($record->description)) {
                $skipped++;
                continue;
            }

            if (! $dry) {
                $record->update(['description' => $description]);
            }

            $this->line("  <info>✓</info> {$name}");
            $updated++;
        }

        $verb = $dry ? 'Would update' : 'Updated';
        $this->newLine();
        $this->info("{$verb}: {$updated}, Skipped (existing): {$skipped}, Not found: {$missing}");

        return self::SUCCESS;
    }
}
