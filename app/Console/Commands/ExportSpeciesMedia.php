<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Exports approved species/subspecies media records to a portable JSON file
 * for import into the production PostgreSQL database.
 *
 * The export resolves mediable_id → scientific name so the import can locate
 * the correct record by name rather than ID (IDs may differ between environments).
 *
 * Usage:
 *   php artisan media:export-species
 *   php artisan media:export-species --output=exports/species_media_2026-05-04.json
 *   php artisan media:export-species --all   (include user-uploaded media too)
 */
class ExportSpeciesMedia extends Command
{
    protected $signature = 'media:export-species
        {--output=exports/species_media.json : Output path relative to storage/app/}
        {--all : Export all approved media, not just Wikipedia-sourced (source_url set)}';

    protected $description = 'Export approved species/subspecies media to JSON for production import';

    public function handle(): int
    {
        $outputPath = $this->option('output');
        $all        = (bool) $this->option('all');

        $query = Media::query()
            ->whereIn('mediable_type', [Species::class, Subspecies::class])
            ->where('moderation_status', 'approved')
            ->with('mediable');

        if (! $all) {
            $query->whereNotNull('source_url');
        }

        $media = $query->get();

        $this->info("Found {$media->count()} media records to export.");

        $records = $media->map(function (Media $m) {
            if (! $m->mediable) {
                return null;
            }

            $scientificName = match ($m->mediable_type) {
                Species::class    => $m->mediable->species,
                Subspecies::class => $m->mediable->full_name,
                default           => null,
            };

            if (! $scientificName) {
                return null;
            }

            return [
                'mediable_type'     => $m->mediable_type,
                'mediable_name'     => $scientificName,
                'url'               => $m->url,
                'moderation_status' => $m->moderation_status,
                'source_url'        => $m->source_url,
                'license'           => $m->license,
                'license_url'       => $m->license_url,
                'author'            => $m->author,
                'copyright'         => $m->copyright,
                'title'             => $m->title,
                'created_at'        => $m->created_at?->toIso8601String(),
            ];
        })->filter()->values();

        $dir = dirname($outputPath);
        if (! Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }

        Storage::put($outputPath, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $fullPath = storage_path('app/' . $outputPath);
        $this->info("Exported {$records->count()} records → {$fullPath}");

        return self::SUCCESS;
    }
}
