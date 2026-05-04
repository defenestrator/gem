<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Species;
use App\Models\Subspecies;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Imports species/subspecies media from a JSON export file into the current database.
 * Designed to run on production MySQL after generating the export on the local Postgres instance.
 *
 * Matching strategy: looks up Species/Subspecies by scientific name, not by ID,
 * so environment-specific auto-increment IDs do not matter.
 *
 * Idempotent: records already present (matched by url) are skipped.
 *
 * Usage:
 *   php artisan media:import-species
 *   php artisan media:import-species --input=exports/species_media_2026-05-04.json --dry-run
 */
class ImportSpeciesMedia extends Command
{
    protected $signature = 'media:import-species
        {--input=exports/species_media.json : Input path relative to storage/app/}
        {--dry-run                          : Preview without inserting anything}';

    protected $description = 'Import species/subspecies media from a JSON export (production use)';

    public function handle(): int
    {
        $inputPath = $this->option('input');
        $dry       = (bool) $this->option('dry-run');

        if (! Storage::exists($inputPath)) {
            $this->error('File not found: ' . storage_path('app/' . $inputPath));
            return self::FAILURE;
        }

        $records = json_decode(Storage::get($inputPath), true);
        if (! is_array($records)) {
            $this->error('Invalid JSON.');
            return self::FAILURE;
        }

        $adminId  = User::where('is_admin', true)->value('id');
        $imported = 0;
        $skipped  = 0;
        $failed   = 0;

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        foreach ($records as $record) {
            $mediable = $this->resolveMediable($record);

            if (! $mediable) {
                $this->warn("  Not found: {$record['mediable_name']}");
                $failed++;
                continue;
            }

            // Idempotent: skip if this URL is already in the database
            if (Media::where('url', $record['url'])->exists()) {
                $skipped++;
                continue;
            }

            $this->line("  → {$record['mediable_name']}");

            if (! $dry) {
                $mediable->media()->create([
                    'url'               => $record['url'],
                    'user_id'           => $adminId,
                    'moderation_status' => $record['moderation_status'],
                    'source_url'        => $record['source_url']  ?? null,
                    'license'           => $record['license']     ?? null,
                    'license_url'       => $record['license_url'] ?? null,
                    'author'            => $record['author']      ?? null,
                    'copyright'         => $record['copyright']   ?? null,
                    'title'             => $record['title']       ?? null,
                ]);
            }

            $imported++;
        }

        $this->newLine();
        $this->table(
            ['Imported', 'Skipped (already exists)', 'Failed (name not found)'],
            [[$imported, $skipped, $failed]]
        );

        return self::SUCCESS;
    }

    private function resolveMediable(array $record): mixed
    {
        return match ($record['mediable_type']) {
            Species::class    => Species::where('species', $record['mediable_name'])->first(),
            Subspecies::class => $this->findSubspecies($record['mediable_name']),
            default           => null,
        };
    }

    private function findSubspecies(string $fullName): ?Subspecies
    {
        // full_name format: "Genus species subspecies"
        $parts = explode(' ', $fullName, 3);
        if (count($parts) < 3) {
            return null;
        }

        [$genus, $species, $subspecies] = $parts;

        return Subspecies::where('genus', $genus)
            ->where('species', $species)
            ->where('subspecies', $subspecies)
            ->first();
    }
}
