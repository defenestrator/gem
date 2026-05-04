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
 * Fetches free CC-licensed images for species/subspecies records.
 *
 * Source chain (tried in order, stops at first hit):
 *   1. Wikipedia REST summary API
 *   2. Wikimedia Commons direct file search  (catches species with no WP article)
 *   3. iNaturalist taxa API
 *   4. GBIF species media API
 *
 * reptile-database.org: no public API, contributor images have unclear/mixed
 * licensing — excluded intentionally.
 *
 * Run in batches (idempotent — already-fetched species are skipped automatically):
 *   php artisan species:fetch-images --model=species --limit=100
 *   php artisan species:fetch-images --model=subspecies --limit=100
 *   php artisan species:fetch-images --id=42 [--model=subspecies]
 *   php artisan species:fetch-images --model=all --limit=50 --dry-run
 */
class FetchSpeciesImages extends Command
{
    private const USER_AGENT = 'GemReptiles/1.0 (contact: jeremyblc@gmail.com)';

    protected $signature = 'species:fetch-images
        {--model=species : species | subspecies | all}
        {--limit=50      : Records to process per run}
        {--id=           : Process a single record by ID}
        {--dry-run       : Preview without saving anything}
        {--force         : Re-fetch even if image already exists}
        {--delay=500     : Milliseconds to wait between species requests}';

    protected $description = 'Fetch free CC-licensed images for species and subspecies from multiple sources';

    private int $adminUserId;
    private int $fetched   = 0;
    private int $skipped   = 0;
    private int $notFound  = 0;
    private int $failed    = 0;

    // -----------------------------------------------------------------------

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
            ['Fetched', 'Skipped', 'Not found in any source', 'Upload failed'],
            [[$this->fetched, $this->skipped, $this->notFound, $this->failed]]
        );

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Batch / dispatch
    // -----------------------------------------------------------------------

    private function processBatch(string $model, int $limit, bool $dry, bool $force): void
    {
        if (in_array($model, ['species', 'all'])) {
            $rows = $this->buildQuery(Species::class, $force)->take($limit)->get();
            $this->info("Species: {$rows->count()} records to process (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'species', $dry);
                usleep((int) $this->option('delay') * 1000);
            }
        }

        if (in_array($model, ['subspecies', 'all'])) {
            $rows = $this->buildQuery(Subspecies::class, $force)->take($limit)->get();
            $this->info("Subspecies: {$rows->count()} records to process (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'subspecies', $dry);
                usleep((int) $this->option('delay') * 1000);
            }
        }
    }

    private function processById(string $model, int $id, bool $dry, bool $force): void
    {
        $record = match ($model) {
            'subspecies' => Subspecies::findOrFail($id),
            default      => Species::findOrFail($id),
        };

        if (! $force && $record->media()->where('moderation_status', 'approved')->whereNotNull('source_url')->exists()) {
            $this->line('  Already has a sourced image. Use --force to re-fetch.');
            $this->skipped++;
            return;
        }

        $type = $model === 'subspecies' ? 'subspecies' : 'species';
        $this->processRecord($record, $type, $dry);
    }

    private function buildQuery(string $modelClass, bool $force)
    {
        $q = $modelClass::query();
        if (! $force) {
            $q->whereDoesntHave('media', fn ($m) =>
                $m->approved()->whereNotNull('source_url')
            );
        }
        return $q;
    }

    // -----------------------------------------------------------------------
    // Per-record logic
    // -----------------------------------------------------------------------

    private function processRecord(mixed $record, string $type, bool $dry): void
    {
        $name = $type === 'subspecies' ? $record->full_name : $record->species;
        $this->line("  → <info>{$name}</info>");

        $imageData = $this->findImage($name);

        if ($imageData === null) {
            $this->line("    <comment>No CC-licensed image found in any source.</comment>");
            $this->notFound++;
            return;
        }

        $this->line("    [{$imageData['source']}] {$imageData['license']}  |  " . Str::limit($imageData['author'], 60));

        if ($dry) {
            $this->line("    [dry-run] {$imageData['download_url']}");
            $this->fetched++;
            return;
        }

        $bytes = $this->download($imageData['download_url']);
        if ($bytes === null) {
            $this->warn("    Download failed.");
            $this->failed++;
            return;
        }

        $ext      = strtolower(pathinfo(parse_url($imageData['download_url'], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
        $path     = "{$type}/{$record->id}/" . Str::slug($name) . ".{$ext}";

        try {
            Storage::disk('s3')->put($path, $bytes, 'public');
        } catch (\Throwable $e) {
            $this->warn("    S3 upload failed: {$e->getMessage()}");
            $this->failed++;
            return;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
        $s3 = Storage::disk('s3');

        $record->media()->create([
            'url'               => $s3->url($path),
            'user_id'           => $this->adminUserId,
            'moderation_status' => 'approved',
            'source_url'        => $imageData['source_url'],
            'license'           => $imageData['license'],
            'license_url'       => $imageData['license_url'],
            'author'            => $imageData['author'],
            'copyright'         => $imageData['author'],
            'title'             => $imageData['title'],
        ]);

        $this->line("    <info>Saved:</info> {$path}");
        $this->fetched++;
    }

    // -----------------------------------------------------------------------
    // Source chain
    // -----------------------------------------------------------------------

    private function findImage(string $scientificName): ?array
    {
        $sources = [
            'Wikipedia'         => fn () => $this->fromWikipedia($scientificName),
            'Wikimedia Commons' => fn () => $this->fromWikimediaCommons($scientificName),
            'iNaturalist'       => fn () => $this->fromINaturalist($scientificName),
            'GBIF'              => fn () => $this->fromGbif($scientificName),
        ];

        foreach ($sources as $sourceName => $fetch) {
            try {
                $result = $fetch();
                if ($result !== null) {
                    return array_merge($result, ['source' => $sourceName]);
                }
            } catch (\Throwable $e) {
                $this->line("    [{$sourceName}] {$e->getMessage()}");
            }
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Source 1 — Wikipedia REST summary
    // -----------------------------------------------------------------------

    private function fromWikipedia(string $name): ?array
    {
        $title = str_replace(' ', '_', $name);

        $res = $this->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$title}");
        if ($res === null || ($res['status'] ?? 200) === 404) {
            return null;
        }

        $downloadUrl = $res['thumbnail']['source']
            ?? $res['originalimage']['source']
            ?? null;

        if (! $downloadUrl) {
            return null;
        }

        $sourceUrl = $res['content_urls']['desktop']['page']
            ?? "https://en.wikipedia.org/wiki/{$title}";

        $attr = $this->wikimediaCommonsAttribution($downloadUrl);

        return [
            'download_url' => $downloadUrl,
            'source_url'   => $sourceUrl,
            'license'      => $attr['license']     ?? 'Unknown',
            'license_url'  => $attr['license_url'] ?? null,
            'author'       => $attr['artist']      ?? 'Wikipedia contributor',
            'title'        => $attr['title']       ?? basename(parse_url($downloadUrl, PHP_URL_PATH)),
        ];
    }

    // -----------------------------------------------------------------------
    // Source 2 — Wikimedia Commons direct file search
    // Catches species that have Commons images but no English Wikipedia article.
    // -----------------------------------------------------------------------

    private function fromWikimediaCommons(string $name): ?array
    {
        $res = $this->get('https://commons.wikimedia.org/w/api.php', [
            'action'      => 'query',
            'list'        => 'search',
            'srsearch'    => "\"{$name}\" filetype:bitmap",
            'srnamespace' => 6,
            'srlimit'     => 5,
            'format'      => 'json',
        ]);

        if (! $res) {
            return null;
        }

        foreach ($res['query']['search'] ?? [] as $result) {
            $fileTitle = $result['title']; // "File:Python regius.jpg"

            $infoRes = $this->get('https://commons.wikimedia.org/w/api.php', [
                'action'     => 'query',
                'titles'     => $fileTitle,
                'prop'       => 'imageinfo',
                'iiprop'     => 'url|extmetadata',
                'iiurlwidth' => 800,
                'format'     => 'json',
            ]);

            if (! $infoRes) {
                continue;
            }

            $pages = $infoRes['query']['pages'] ?? [];
            $page  = reset($pages);
            $info  = $page['imageinfo'][0] ?? null;
            if (! $info) {
                continue;
            }

            $meta    = $info['extmetadata'] ?? [];
            $license = $meta['LicenseShortName']['value'] ?? null;

            if (! $license || ! $this->isCcLicense($license)) {
                continue;
            }

            $url = $info['thumburl'] ?? $info['url'] ?? null;
            if (! $url) {
                continue;
            }

            return [
                'download_url' => $url,
                'source_url'   => $info['descriptionurl'] ?? "https://commons.wikimedia.org/wiki/{$fileTitle}",
                'license'      => $license,
                'license_url'  => $meta['LicenseUrl']['value'] ?? null,
                'author'       => strip_tags($meta['Artist']['value'] ?? ''),
                'title'        => strip_tags($meta['ImageDescription']['value'] ?? basename($fileTitle)),
            ];
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Source 3 — iNaturalist taxa API
    // -----------------------------------------------------------------------

    private function fromINaturalist(string $name): ?array
    {
        $res = $this->get('https://api.inaturalist.org/v1/taxa', [
            'q'          => $name,
            'rank'       => 'species,subspecies',
            'per_page'   => 1,
            'order_by'   => 'observations_count',
            'order'      => 'desc',
        ]);

        if (! $res) {
            return null;
        }

        $taxon = $res['results'][0] ?? null;
        if (! $taxon) {
            return null;
        }

        // Require exact name match — API may return close relatives
        if (strtolower($taxon['name']) !== strtolower($name)) {
            return null;
        }

        foreach ($taxon['taxon_photos'] ?? [] as $tp) {
            $photo = $tp['photo'] ?? null;
            if (! $photo) {
                continue;
            }

            $licenseCode = $photo['license_code'] ?? null;
            if (! $licenseCode || $licenseCode === 'all-rights-reserved') {
                continue;
            }

            // Square → medium (roughly 440×440 → reasonable quality)
            $url = str_replace('/square.', '/medium.', $photo['url'] ?? '');
            if (! $url) {
                continue;
            }

            return [
                'download_url' => $url,
                'source_url'   => "https://www.inaturalist.org/taxa/{$taxon['id']}",
                'license'      => $this->normalizeLicenseCode($licenseCode),
                'license_url'  => $this->licenseUrlFromCode($licenseCode),
                'author'       => strip_tags($photo['attribution'] ?? 'iNaturalist contributor'),
                'title'        => $taxon['name'],
            ];
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Source 4 — GBIF species media
    // -----------------------------------------------------------------------

    private function fromGbif(string $name): ?array
    {
        // Species match endpoint returns a direct key with confidence score
        $matchRes = $this->get('https://api.gbif.org/v1/species/match', [
            'name'   => $name,
            'strict' => 'false',
        ]);

        if (! $matchRes || ($matchRes['matchType'] ?? '') === 'NONE') {
            return null;
        }

        // Require high confidence and name overlap
        $confidence    = (int) ($matchRes['confidence'] ?? 0);
        $canonicalName = $matchRes['canonicalName'] ?? '';

        if ($confidence < 90 || stripos($canonicalName, explode(' ', $name)[0]) === false) {
            return null;
        }

        $key = $matchRes['usageKey'] ?? null;
        if (! $key) {
            return null;
        }

        $mediaRes = $this->get("https://api.gbif.org/v1/species/{$key}/media", [
            'type'  => 'StillImage',
            'limit' => 10,
        ]);

        if (! $mediaRes) {
            return null;
        }

        foreach ($mediaRes['results'] ?? [] as $item) {
            $license = $item['license'] ?? '';
            if (! str_contains($license, 'creativecommons.org')) {
                continue;
            }

            $url = $item['identifier'] ?? null;
            if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            return [
                'download_url' => $url,
                'source_url'   => $item['references'] ?? "https://www.gbif.org/species/{$key}",
                'license'      => $this->parseLicenseUrl($license),
                'license_url'  => $license,
                'author'       => $item['rightsHolder'] ?? ($item['creator'] ?? 'GBIF contributor'),
                'title'        => $item['title'] ?? ($item['description'] ?? $name),
            ];
        }

        return null;
    }

    // -----------------------------------------------------------------------
    // Shared Wikimedia Commons attribution lookup
    // -----------------------------------------------------------------------

    private function wikimediaCommonsAttribution(string $imageUrl): array
    {
        $path     = parse_url($imageUrl, PHP_URL_PATH);
        $filename = null;

        if (str_contains($path, '/thumb/')) {
            if (preg_match('#/thumb/[^/]+/[^/]+/([^/]+)/#', $path, $m)) {
                $filename = urldecode($m[1]);
            }
        } else {
            $filename = urldecode(basename($path));
        }

        if (! $filename || ! str_contains($imageUrl, 'wikimedia.org')) {
            return [];
        }

        $res = $this->get('https://commons.wikimedia.org/w/api.php', [
            'action' => 'query',
            'titles' => "File:{$filename}",
            'prop'   => 'imageinfo',
            'iiprop' => 'extmetadata',
            'format' => 'json',
        ]);

        if (! $res) {
            return [];
        }

        $pages = $res['query']['pages'] ?? [];
        $page  = reset($pages);
        $meta  = $page['imageinfo'][0]['extmetadata'] ?? [];

        return [
            'license'     => $meta['LicenseShortName']['value'] ?? null,
            'license_url' => $meta['LicenseUrl']['value'] ?? null,
            'artist'      => strip_tags($meta['Artist']['value'] ?? ''),
            'title'       => strip_tags($meta['ObjectName']['value'] ?? ($meta['ImageDescription']['value'] ?? '')),
        ];
    }

    // -----------------------------------------------------------------------
    // HTTP helper
    // -----------------------------------------------------------------------

    private function get(string $url, array $params = []): ?array
    {
        $req = Http::withUserAgent(self::USER_AGENT)->timeout(15);
        $res = $params ? $req->get($url, $params) : $req->get($url);

        if ($res->status() === 404) {
            return null;
        }

        if (! $res->successful()) {
            $this->line("    HTTP {$res->status()} from {$url}");
            return null;
        }

        return $res->json();
    }

    private function download(string $url): ?string
    {
        try {
            $res = Http::withUserAgent(self::USER_AGENT)->timeout(30)->get($url);
            return $res->successful() ? $res->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    // -----------------------------------------------------------------------
    // License helpers
    // -----------------------------------------------------------------------

    private function isCcLicense(string $license): bool
    {
        $l = strtolower($license);
        return str_starts_with($l, 'cc') || str_starts_with($l, 'public domain');
    }

    private function normalizeLicenseCode(string $code): string
    {
        return match (strtolower($code)) {
            'cc0'          => 'CC0 1.0',
            'cc-by'        => 'CC BY 4.0',
            'cc-by-sa'     => 'CC BY-SA 4.0',
            'cc-by-nd'     => 'CC BY-ND 4.0',
            'cc-by-nc'     => 'CC BY-NC 4.0',
            'cc-by-nc-sa'  => 'CC BY-NC-SA 4.0',
            'cc-by-nc-nd'  => 'CC BY-NC-ND 4.0',
            default        => strtoupper($code),
        };
    }

    private function licenseUrlFromCode(string $code): ?string
    {
        return match (strtolower($code)) {
            'cc0'         => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'cc-by'       => 'https://creativecommons.org/licenses/by/4.0/',
            'cc-by-sa'    => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'cc-by-nd'    => 'https://creativecommons.org/licenses/by-nd/4.0/',
            'cc-by-nc'    => 'https://creativecommons.org/licenses/by-nc/4.0/',
            'cc-by-nc-sa' => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
            'cc-by-nc-nd' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/',
            default       => null,
        };
    }

    private function parseLicenseUrl(string $url): string
    {
        if (preg_match('#creativecommons\.org/licenses/([^/]+)/([^/]+)#', $url, $m)) {
            return 'CC ' . strtoupper($m[1]) . ' ' . $m[2];
        }
        if (str_contains($url, 'publicdomain/zero')) {
            return 'CC0';
        }
        return $url;
    }
}
