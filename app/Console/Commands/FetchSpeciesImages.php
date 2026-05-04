<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fetches free CC-licensed images from Wikimedia Commons for species and subspecies.
 *
 * reptile-database.org was considered but has no public API and contributor images
 * have unclear/mixed licensing. Wikimedia Commons provides machine-readable CC license
 * metadata and a stable attribution API — the correct source for this purpose.
 *
 * Run in batches:
 *   php artisan species:fetch-images --model=species --limit=100
 *   php artisan species:fetch-images --model=subspecies --limit=100
 *   php artisan species:fetch-images --id=42  (single record, species by default)
 *
 * Idempotent: skips records that already have an approved, source_url-tagged media entry.
 * Use --force to re-fetch.
 */
class FetchSpeciesImages extends Command
{
    protected $signature = 'species:fetch-images
        {--model=species    : species | subspecies | all}
        {--limit=50         : Records to process per run}
        {--id=              : Process a single record by ID}
        {--dry-run          : Preview without saving anything}
        {--force            : Re-fetch even if image already exists}
        {--delay=500        : Milliseconds between Wikipedia requests}';

    protected $description = 'Fetch free Wikimedia Commons images for species and subspecies records';

    private int $adminUserId;
    private int $fetched  = 0;
    private int $skipped  = 0;
    private int $notFound = 0;
    private int $failed   = 0;

    public function handle(): int
    {
        $admin = User::where('is_admin', true)->first();
        if (! $admin) {
            $this->error('No admin user found.');
            return self::FAILURE;
        }
        $this->adminUserId = $admin->id;

        $model = $this->option('model');
        $limit = (int) $this->option('limit');
        $id    = $this->option('id');
        $dry   = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        if ($id !== null) {
            $this->processById($model, (int) $id, $dry, $force);
        } else {
            $this->processBatch($model, $limit, $dry, $force);
        }

        $this->newLine();
        $this->table(
            ['Fetched', 'Skipped', 'Not found on Wikipedia', 'Failed'],
            [[$this->fetched, $this->skipped, $this->notFound, $this->failed]]
        );

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------

    private function processBatch(string $model, int $limit, bool $dry, bool $force): void
    {
        if (in_array($model, ['species', 'all'])) {
            $rows = $this->buildQuery(Species::class, $force)->take($limit)->get();
            $this->info("Species: processing {$rows->count()} records (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'species', $dry, $force);
            }
        }

        if (in_array($model, ['subspecies', 'all'])) {
            $rows = $this->buildQuery(Subspecies::class, $force)->take($limit)->get();
            $this->info("Subspecies: processing {$rows->count()} records (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'subspecies', $dry, $force);
            }
        }
    }

    private function processById(string $model, int $id, bool $dry, bool $force): void
    {
        $record = match ($model) {
            'subspecies' => Subspecies::findOrFail($id),
            default      => Species::findOrFail($id),
        };
        $type = ($model === 'subspecies') ? 'subspecies' : 'species';
        $this->processRecord($record, $type, $dry, $force);
    }

    private function buildQuery(string $modelClass, bool $force)
    {
        $q = $modelClass::query();
        if (! $force) {
            $q->whereDoesntHave('media', fn ($m) =>
                $m->where('moderation_status', 'approved')->whereNotNull('source_url')
            );
        }
        return $q;
    }

    // -----------------------------------------------------------------------

    private function processRecord(mixed $record, string $type, bool $dry, bool $force): void
    {
        $name = $type === 'subspecies' ? $record->full_name : $record->species;
        $this->line("  → <info>{$name}</info>");

        $imageData = $this->fetchWikipediaImage($name);

        if ($imageData === null) {
            $this->line("    <comment>No Wikipedia image found.</comment>");
            $this->notFound++;
            return;
        }

        $this->line("    License: {$imageData['license']}  |  Author: " . Str::limit($imageData['author'], 60));

        if ($dry) {
            $this->line("    [dry-run] Would download: {$imageData['download_url']}");
            $this->fetched++;
            return;
        }

        $imageBytes = $this->downloadImage($imageData['download_url']);
        if ($imageBytes === null) {
            $this->warn("    Download failed.");
            $this->failed++;
            return;
        }

        $ext      = strtolower(pathinfo(parse_url($imageData['download_url'], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
        $filename = "{$type}/{$record->id}/" . Str::slug($name) . ".{$ext}";

        try {
            Storage::disk('s3')->put($filename, $imageBytes, 'public');
        } catch (\Throwable $e) {
            $this->warn("    S3 upload failed: {$e->getMessage()}");
            $this->failed++;
            return;
        }

        $record->media()->create([
            'url'               => Storage::disk('s3')->url($filename),
            'user_id'           => $this->adminUserId,
            'moderation_status' => 'approved',
            'source_url'        => $imageData['source_url'],
            'license'           => $imageData['license'],
            'license_url'       => $imageData['license_url'],
            'author'            => $imageData['author'],
            'copyright'         => $imageData['author'],
            'title'             => $imageData['title'],
        ]);

        $this->line("    <info>Saved:</info> {$filename}");
        $this->fetched++;

        usleep((int) $this->option('delay') * 1000);
    }

    // -----------------------------------------------------------------------
    // Wikipedia / Wikimedia Commons API
    // -----------------------------------------------------------------------

    private function fetchWikipediaImage(string $scientificName): ?array
    {
        $title = str_replace(' ', '_', $scientificName);

        try {
            $res = Http::withUserAgent('GemReptiles/1.0 (contact: jeremyblc@gmail.com)')
                ->timeout(15)
                ->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$title}");

            if ($res->status() === 404) {
                return null;
            }

            if (! $res->successful()) {
                $this->warn("    Wikipedia returned HTTP {$res->status()}");
                return null;
            }

            $summary = $res->json();

            // Prefer a moderate-size thumbnail over the multi-megabyte original
            $downloadUrl = $summary['thumbnail']['source']
                ?? $summary['originalimage']['source']
                ?? null;

            if (! $downloadUrl) {
                return null;
            }

            $sourceUrl = $summary['content_urls']['desktop']['page']
                ?? "https://en.wikipedia.org/wiki/{$title}";

            $attr = $this->fetchCommonsAttribution($downloadUrl);

            return [
                'download_url' => $downloadUrl,
                'source_url'   => $sourceUrl,
                'license'      => $attr['license']      ?? 'Unknown',
                'license_url'  => $attr['license_url']  ?? null,
                'author'       => $attr['artist']       ?? 'Wikipedia contributor',
                'title'        => $attr['title']        ?? basename(parse_url($downloadUrl, PHP_URL_PATH)),
            ];
        } catch (\Throwable $e) {
            $this->warn("    Wikipedia API error: {$e->getMessage()}");
            return null;
        }
    }

    private function fetchCommonsAttribution(string $imageUrl): array
    {
        $path     = parse_url($imageUrl, PHP_URL_PATH);
        $filename = null;

        if (str_contains($path, '/thumb/')) {
            // /wikipedia/commons/thumb/{h1}/{h2}/{filename}/{size}-{filename}
            if (preg_match('#/thumb/[^/]+/[^/]+/([^/]+)/#', $path, $m)) {
                $filename = urldecode($m[1]);
            }
        } else {
            $filename = urldecode(basename($path));
        }

        if (! $filename || ! str_contains($imageUrl, 'wikimedia.org')) {
            return [];
        }

        try {
            $res = Http::withUserAgent('GemReptiles/1.0 (contact: jeremyblc@gmail.com)')
                ->timeout(10)
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action'  => 'query',
                    'titles'  => "File:{$filename}",
                    'prop'    => 'imageinfo',
                    'iiprop'  => 'extmetadata',
                    'format'  => 'json',
                ]);

            if (! $res->successful()) {
                return [];
            }

            $pages = $res->json('query.pages', []);
            $page  = reset($pages);
            $meta  = $page['imageinfo'][0]['extmetadata'] ?? [];

            return [
                'license'     => $meta['LicenseShortName']['value'] ?? ($meta['License']['value'] ?? null),
                'license_url' => $meta['LicenseUrl']['value'] ?? null,
                'artist'      => strip_tags($meta['Artist']['value'] ?? ''),
                'title'       => strip_tags($meta['ObjectName']['value'] ?? $meta['ImageDescription']['value'] ?? ''),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function downloadImage(string $url): ?string
    {
        try {
            $res = Http::withUserAgent('GemReptiles/1.0 (contact: jeremyblc@gmail.com)')
                ->timeout(30)
                ->get($url);

            return $res->successful() ? $res->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
