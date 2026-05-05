<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fetches 1–8 free CC-licensed images per species/subspecies record.
 *
 * Source chain (all sources queried per species until 8 images are collected):
 *   1. Wikipedia REST summary API
 *   2. Wikimedia Commons direct file search
 *   3. iNaturalist taxa API
 *   4. GBIF species media API
 *   5. Reptile Database (HTML scrape)
 *   6. ARMI USGS gallery (public domain)
 *   7. BioLib.cz (rate-limited: 2 s between requests)
 *
 * Resumable — skips records that already have 8 sourced images; fills up to 8
 * for records with fewer. Use --force to process all records regardless.
 *
 *   php artisan species:fetch-images --model=species --limit=100
 *   php artisan species:fetch-images --model=subspecies --limit=100
 *   php artisan species:fetch-images --id=42 [--model=subspecies]
 *   php artisan species:fetch-images --model=all --limit=50 --dry-run
 */
class FetchSpeciesImages extends Command
{
    private const USER_AGENT  = 'GemReptiles/1.0 (contact: jeremyblc@gmail.com)';


    protected $signature = 'species:fetch-images
        {--model=species : species | subspecies | all}
        {--limit=1000    : Records to process per run (ignored when --queue is set)}
        {--id=           : Process a single record by ID}
        {--queue         : Dispatch a queued job per record instead of processing inline}
        {--dry-run       : Preview without saving anything}
        {--force         : Process records regardless of existing image count}
        {--max=8         : Maximum images to collect per taxon (1–8)}
        {--delay=500     : Milliseconds to wait between species requests (inline only)}';

    protected $description = 'Fetch 1–8 free CC-licensed images per species/subspecies from multiple sources';

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
            $this->processById($model, (int) $id, $dry);
        } elseif ($this->option('queue')) {
            $this->dispatchAll($model, $force, $dry);
            return self::SUCCESS;
        } else {
            $this->processBatch($model, $limit, $dry, $force);
        }

        $this->newLine();
        $this->table(
            ['Images saved', 'Records skipped (full)', 'No image found', 'Upload errors'],
            [[$this->fetched, $this->skipped, $this->notFound, $this->failed]]
        );

        Log::info('fetch-images: run complete', [
            'saved'    => $this->fetched,
            'skipped'  => $this->skipped,
            'notFound' => $this->notFound,
            'failed'   => $this->failed,
        ]);

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Batch / dispatch
    // -----------------------------------------------------------------------

    private function processBatch(string $model, int $limit, bool $dry, bool $force): void
    {
        if (in_array($model, ['species', 'all'])) {
            $rows = $this->buildQuery(Species::class, $force)->take($limit)->get();
            $this->info("Species: {$rows->count()} records queued (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'species', $dry);
                usleep((int) $this->option('delay') * 1000);
            }
        }

        if (in_array($model, ['subspecies', 'all'])) {
            $rows = $this->buildQuery(Subspecies::class, $force)->take($limit)->get();
            $this->info("Subspecies: {$rows->count()} records queued (limit {$limit})");
            foreach ($rows as $row) {
                $this->processRecord($row, 'subspecies', $dry);
                usleep((int) $this->option('delay') * 1000);
            }
        }
    }

    private function dispatchAll(string $model, bool $force, bool $dry): void
    {
        $max       = $this->maxImages();
        $total     = 0;

        $dispatch = function (string $modelClass) use ($force, $dry, $max, &$total) {
            $this->buildQuery($modelClass, $force)
                ->select('id')
                ->chunkById(500, function ($rows) use ($modelClass, $dry, $max, &$total) {
                    foreach ($rows as $row) {
                        if (! $dry) {
                            \App\Jobs\FetchTaxonImageJob::dispatch($modelClass, $row->id, $max);
                        }
                        $total++;
                    }
                });
        };

        if (in_array($model, ['species', 'all']))    $dispatch(Species::class);
        if (in_array($model, ['subspecies', 'all'])) $dispatch(Subspecies::class);

        $label = $dry ? '[dry-run] Would dispatch' : 'Dispatched';
        $this->info("{$label} {$total} jobs → queue: species-images");
    }

    private function processById(string $model, int $id, bool $dry): void
    {
        $record = match ($model) {
            'subspecies' => Subspecies::findOrFail($id),
            default      => Species::findOrFail($id),
        };
        $type = $model === 'subspecies' ? 'subspecies' : 'species';
        $this->processRecord($record, $type, $dry);
    }

    private function maxImages(): int
    {
        return max(1, min(8, (int) $this->option('max')));
    }

    /**
     * Returns records ordered by sourced-image count ascending (0-image species first).
     * Records already at --max are excluded so runs always make forward progress
     * through unprocessed species before circling back to top up partially-filled ones.
     */
    private function buildQuery(string $modelClass, bool $force)
    {
        $table = (new $modelClass)->getTable();
        $q     = $modelClass::query();

        $countSql = "(SELECT COUNT(*) FROM media
                      WHERE media.mediable_type = ?
                        AND media.mediable_id   = {$table}.id
                        AND media.moderation_status = 'approved'
                        AND media.source_url IS NOT NULL)";

        if (! $force) {
            $q->whereRaw("{$countSql} < ?", [$modelClass, $this->maxImages()]);
        }

        // 0-image records always come first; partially-filled records follow
        $q->orderByRaw("{$countSql} ASC", [$modelClass]);

        return $q;
    }

    // -----------------------------------------------------------------------
    // Per-record logic
    // -----------------------------------------------------------------------

    private function processRecord(mixed $record, string $type, bool $dry): void
    {
        $name = $type === 'subspecies' ? $record->full_name : $record->species;

        // How many more images does this record need?
        $existing = $record->media()
            ->where('moderation_status', 'approved')
            ->whereNotNull('source_url')
            ->get(['id', 'source_url']);

        $needed = max(0, $this->maxImages() - $existing->count());

        if ($needed === 0) {
            $this->skipped++;
            return;
        }

        $existingSourceUrls = $existing->pluck('source_url')->filter()->all();

        $this->line("  → <info>{$name}</info> (have {$existing->count()}, need up to {$needed} more)");
        Log::info("fetch-images: processing {$name}", ['have' => $existing->count(), 'need' => $needed, 'type' => $type]);

        $images = $this->findImages($name, $needed, $existingSourceUrls);

        if (empty($images)) {
            $this->line("    <comment>No CC-licensed images found in any source.</comment>");
            Log::info("fetch-images: no images found for {$name}");
            $this->notFound++;
            return;
        }

        $this->line("    Found " . count($images) . " new image(s)");

        $index = $existing->count() + 1;

        foreach ($images as $imageData) {
            if ($dry) {
                $this->line("    [dry-run] [{$imageData['source']}] {$imageData['download_url']}");
                $this->fetched++;
                $index++;
                continue;
            }

            $bytes = $this->download($imageData['download_url']);
            if ($bytes === null) {
                $this->warn("    Download failed: {$imageData['download_url']}");
                Log::warning("fetch-images: download failed for {$name}", ['url' => $imageData['download_url']]);
                $this->failed++;
                continue;
            }

            $ext  = strtolower(pathinfo(parse_url($imageData['download_url'], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
            $path = "{$type}/{$record->id}/" . Str::slug($name) . "-{$index}.{$ext}";

            try {
                Storage::disk('s3')->put($path, $bytes, 'public');
            } catch (\Throwable $e) {
                $this->warn("    S3 upload failed: {$e->getMessage()}");
                Log::error("fetch-images: S3 upload failed for {$name}", ['path' => $path, 'error' => $e->getMessage()]);
                $this->failed++;
                continue;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
            $s3 = Storage::disk('s3');

            $record->media()->create([
                'url'               => $s3->url($path),
                'user_id'           => $this->adminUserId,
                'moderation_status' => 'approved',
                'source_url'        => $imageData['download_url'],  // image origin URL for dedup
                'license'           => $imageData['license'],
                'license_url'       => $imageData['license_url'],
                'author'            => $imageData['author'],
                'copyright'         => $imageData['author'],
                'title'             => $imageData['title'],
            ]);

            $this->line("    <info>[{$imageData['source']}]</info> saved → {$path}");
            Log::info("fetch-images: saved image for {$name}", ['source' => $imageData['source'], 'path' => $path]);
            $this->fetched++;
            $index++;
        }
    }

    // -----------------------------------------------------------------------
    // Source chain — collects up to $needed images from all sources
    // -----------------------------------------------------------------------

    private function findImages(string $name, int $needed, array $existingSourceUrls): array
    {
        $sources = [
            'Wikipedia'          => fn (int $n) => $this->fromWikipedia($name),
            'Wikimedia Commons'  => fn (int $n) => $this->fromWikimediaCommons($name, $n),
            'iNaturalist'        => fn (int $n) => $this->fromINaturalist($name, $n),
            'GBIF'               => fn (int $n) => $this->fromGbif($name, $n),
            'Reptile Database'   => fn (int $n) => $this->fromReptileDatabase($name, $n),
            'ARMI USGS'          => fn (int $n) => $this->fromArmi($name, $n),
            'BioLib'             => fn (int $n) => $this->fromBioLib($name, $n),
        ];

        $collected = [];
        $seen      = array_flip($existingSourceUrls); // download_url → true

        foreach ($sources as $sourceName => $fetch) {
            $remaining = $needed - count($collected);
            if ($remaining <= 0) {
                break;
            }

            try {
                $results = $fetch($remaining);
                foreach ($results as $img) {
                    if (count($collected) >= $needed) {
                        break;
                    }
                    $key = $img['download_url'];
                    if (isset($seen[$key])) {
                        continue; // already saved or seen this run
                    }
                    $seen[$key]  = true;
                    $collected[] = array_merge($img, ['source' => $sourceName]);
                }
            } catch (\Throwable $e) {
                $this->line("    [{$sourceName}] {$e->getMessage()}");
                Log::warning("fetch-images: source error [{$sourceName}] for {$name}", ['error' => $e->getMessage()]);
            }
        }

        return $collected;
    }

    // -----------------------------------------------------------------------
    // Source 1 — Wikipedia REST summary (1 image per article)
    // -----------------------------------------------------------------------

    private function fromWikipedia(string $name): array
    {
        $title = str_replace(' ', '_', $name);
        $res   = $this->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$title}");
        if (! $res) {
            return [];
        }

        $downloadUrl = $res['thumbnail']['source'] ?? $res['originalimage']['source'] ?? null;
        if (! $downloadUrl) {
            return [];
        }

        $sourceUrl = $res['content_urls']['desktop']['page'] ?? "https://en.wikipedia.org/wiki/{$title}";
        $attr      = $this->wikimediaCommonsAttribution($downloadUrl);

        return [[
            'download_url' => $downloadUrl,
            'source_url'   => $sourceUrl,
            'license'      => $attr['license']     ?? 'Unknown',
            'license_url'  => $attr['license_url'] ?? null,
            'author'       => $attr['artist']      ?? 'Wikipedia contributor',
            'title'        => $attr['title']       ?? basename(parse_url($downloadUrl, PHP_URL_PATH)),
        ]];
    }

    // -----------------------------------------------------------------------
    // Source 2 — Wikimedia Commons direct file search
    // -----------------------------------------------------------------------

    private function fromWikimediaCommons(string $name, int $limit): array
    {
        $res = $this->get('https://commons.wikimedia.org/w/api.php', [
            'action'      => 'query',
            'list'        => 'search',
            'srsearch'    => "\"{$name}\" filetype:bitmap",
            'srnamespace' => 6,
            'srlimit'     => $limit,
            'format'      => 'json',
        ]);

        if (! $res) {
            return [];
        }

        $images = [];

        foreach ($res['query']['search'] ?? [] as $result) {
            if (count($images) >= $limit) {
                break;
            }

            $fileTitle = $result['title'];

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
            if (! $this->isAcceptableImage($license ?? null)) {
                continue;
            }

            $url = $info['thumburl'] ?? $info['url'] ?? null;
            if (! $url) {
                continue;
            }

            $images[] = [
                'download_url' => $url,
                'source_url'   => $info['descriptionurl'] ?? "https://commons.wikimedia.org/wiki/{$fileTitle}",
                'license'      => $license,
                'license_url'  => $meta['LicenseUrl']['value'] ?? null,
                'author'       => strip_tags($meta['Artist']['value'] ?? ''),
                'title'        => strip_tags($meta['ImageDescription']['value'] ?? basename($fileTitle)),
            ];
        }

        return $images;
    }

    // -----------------------------------------------------------------------
    // Source 3 — iNaturalist taxa API
    // -----------------------------------------------------------------------

    private function fromINaturalist(string $name, int $limit): array
    {
        $res = $this->get('https://api.inaturalist.org/v1/taxa', [
            'q'        => $name,
            'rank'     => 'species,subspecies',
            'per_page' => 1,
            'order_by' => 'observations_count',
            'order'    => 'desc',
        ]);

        if (! $res) {
            return [];
        }

        $taxon = $res['results'][0] ?? null;
        if (! $taxon || strtolower($taxon['name']) !== strtolower($name)) {
            return [];
        }

        $images = [];

        foreach ($taxon['taxon_photos'] ?? [] as $tp) {
            if (count($images) >= $limit) {
                break;
            }

            $photo       = $tp['photo'] ?? null;
            $licenseCode = $photo['license_code'] ?? null;

            if (! $photo || $licenseCode === 'all-rights-reserved') {
                continue;
            }

            $url = str_replace('/square.', '/medium.', $photo['url'] ?? '');
            if (! $url) {
                continue;
            }

            $images[] = [
                'download_url' => $url,
                'source_url'   => "https://www.inaturalist.org/taxa/{$taxon['id']}",
                'license'      => $licenseCode ? $this->normalizeLicenseCode($licenseCode) : 'Unspecified',
                'license_url'  => $licenseCode ? $this->licenseUrlFromCode($licenseCode) : null,
                'author'       => strip_tags($photo['attribution'] ?? 'iNaturalist contributor'),
                'title'        => $taxon['name'],
            ];
        }

        return $images;
    }

    // -----------------------------------------------------------------------
    // Source 4 — GBIF species media
    // -----------------------------------------------------------------------

    private function fromGbif(string $name, int $limit): array
    {
        $matchRes = $this->get('https://api.gbif.org/v1/species/match', [
            'name'   => $name,
            'strict' => 'false',
        ]);

        if (! $matchRes || ($matchRes['matchType'] ?? '') === 'NONE') {
            return [];
        }

        $confidence    = (int) ($matchRes['confidence'] ?? 0);
        $canonicalName = $matchRes['canonicalName'] ?? '';

        if ($confidence < 90 || stripos($canonicalName, explode(' ', $name)[0]) === false) {
            return [];
        }

        $key = $matchRes['usageKey'] ?? null;
        if (! $key) {
            return [];
        }

        $mediaRes = $this->get("https://api.gbif.org/v1/species/{$key}/media", [
            'type'  => 'StillImage',
            'limit' => $limit,
        ]);

        if (! $mediaRes) {
            return [];
        }

        $images = [];

        foreach ($mediaRes['results'] ?? [] as $item) {
            if (count($images) >= $limit) {
                break;
            }

            $license = $item['license'] ?? '';
            if (! $this->isAcceptableImage($license ?: null)) {
                continue;
            }

            $url = $item['identifier'] ?? null;
            if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $images[] = [
                'download_url' => $url,
                'source_url'   => $item['references'] ?? "https://www.gbif.org/species/{$key}",
                'license'      => $this->parseLicenseUrl($license),
                'license_url'  => $license,
                'author'       => $item['rightsHolder'] ?? ($item['creator'] ?? 'GBIF contributor'),
                'title'        => $item['title'] ?? ($item['description'] ?? $name),
            ];
        }

        return $images;
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
    // Source 5 — Reptile Database (HTML scrape)
    // -----------------------------------------------------------------------

    private function fromReptileDatabase(string $name, int $limit): array
    {
        $parts = explode(' ', trim($name));
        if (count($parts) < 2) {
            return [];
        }

        $params = ['genus' => $parts[0], 'species' => $parts[1]];
        if (isset($parts[2])) {
            $params['subspecies'] = $parts[2];
        }

        $html = $this->getHtml('https://reptile-database.reptarium.cz/species', $params);
        if (! $html) {
            return [];
        }

        // Images hosted on the site under /content/photo_id/ or similar paths
        preg_match_all(
            '/<img\b[^>]+\bsrc=["\']([^"\']*reptile-database[^"\']+\.(?:jpg|jpeg|png|webp))["\'][^>]*>/i',
            $html,
            $imgMatches
        );

        // Photo credits appear as "© Firstname Lastname" or "Photo: Name"
        preg_match_all('/(?:©|Photo\s*:)\s*([A-Z][A-Za-z\s\.\-]{2,50}?)(?:<|,|\n|&)/u', $html, $creditMatches);

        $sourceUrl = 'https://reptile-database.reptarium.cz/species?' . http_build_query($params);
        $images    = [];

        foreach ($imgMatches[1] as $i => $src) {
            if (count($images) >= $limit) {
                break;
            }

            if (! filter_var($src, FILTER_VALIDATE_URL)) {
                $src = 'https://reptile-database.reptarium.cz' . $src;
            }

            $author = trim($creditMatches[1][$i] ?? 'Reptile Database contributor');

            $images[] = [
                'download_url' => $src,
                'source_url'   => $sourceUrl,
                'license'      => null,
                'license_url'  => null,
                'author'       => $author,
                'title'        => $name,
            ];
        }

        return $images;
    }

    // -----------------------------------------------------------------------
    // Source 6 — ARMI USGS gallery (public domain government images)
    // -----------------------------------------------------------------------

    private function fromArmi(string $name, int $limit): array
    {
        $html = $this->getHtml('https://armi.usgs.gov/gallery/index.php', ['search' => $name]);
        if (! $html) {
            return [];
        }

        // Gallery image links — full-size images are linked from thumbnails
        preg_match_all(
            '/<a[^>]+href=["\']([^"\']+\.(?:jpg|jpeg|png))["\'][^>]*>\s*<img\b/i',
            $html,
            $linkMatches
        );

        // Fall back to direct img src if no linked full-size images found
        if (empty($linkMatches[1])) {
            preg_match_all(
                '/<img\b[^>]+\bsrc=["\']([^"\']+\.(?:jpg|jpeg|png))["\'][^>]*>/i',
                $html,
                $imgMatches
            );
            $srcs = $imgMatches[1] ?? [];
        } else {
            $srcs = $linkMatches[1];
        }

        $images = [];

        foreach ($srcs as $src) {
            if (count($images) >= $limit) {
                break;
            }

            // Skip obvious thumbnails and UI images
            if (preg_match('/thumb|icon|logo|button|header/i', $src)) {
                continue;
            }

            if (! filter_var($src, FILTER_VALIDATE_URL)) {
                $src = 'https://armi.usgs.gov' . $src;
            }

            $images[] = [
                'download_url' => $src,
                'source_url'   => 'https://armi.usgs.gov/gallery/index.php?search=' . urlencode($name),
                'license'      => 'Public Domain',
                'license_url'  => 'https://www.usgs.gov/information-policies-and-instructions/copyrights-and-credits',
                'author'       => 'USGS ARMI',
                'title'        => $name,
            ];
        }

        return $images;
    }

    // -----------------------------------------------------------------------
    // Source 7 — BioLib.cz (rate-limited: 2s between each request)
    // -----------------------------------------------------------------------

    private function fromBioLib(string $name, int $limit): array
    {
        // Step 1 — find taxon ID from search
        sleep(2);
        $searchHtml = $this->getHtml('https://www.biolib.cz/en/taxonsearch/', [
            'action'     => 'show',
            'string'     => $name,
            'searchtype' => 'taxonomyform',
        ]);

        if (! $searchHtml) {
            return [];
        }

        // Taxon links appear as /en/taxon/id12345/
        preg_match('/href="\/en\/taxon\/id(\d+)\/"/i', $searchHtml, $idMatch);
        if (! $idMatch) {
            return [];
        }

        $taxonId = $idMatch[1];

        // Step 2 — fetch image listing for taxon
        sleep(2);
        $listHtml = $this->getHtml("https://www.biolib.cz/en/image/list/taxon{$taxonId}/");
        if (! $listHtml) {
            return [];
        }

        preg_match_all('/href="\/en\/image\/id(\d+)\/"/i', $listHtml, $imgIds);
        if (empty($imgIds[1])) {
            return [];
        }

        $images = [];

        foreach (array_slice($imgIds[1], 0, $limit) as $imgId) {
            // Step 3 — fetch each image detail page
            sleep(2);
            $imgHtml = $this->getHtml("https://www.biolib.cz/en/image/id{$imgId}/");
            if (! $imgHtml) {
                continue;
            }

            // Main image src
            preg_match('/<img\b[^>]+id=["\']bigimage["\'][^>]+src=["\']([^"\']+)["\']/i', $imgHtml, $srcMatch);
            if (! $srcMatch) {
                // Try generic large image pattern
                preg_match('/<img\b[^>]+src=["\']([^"\']+\/images\/[^"\']+\.(?:jpg|jpeg|png))["\']/i', $imgHtml, $srcMatch);
            }
            if (! $srcMatch) {
                continue;
            }

            $src = $srcMatch[1];
            if (! filter_var($src, FILTER_VALIDATE_URL)) {
                $src = 'https://www.biolib.cz' . $src;
            }

            // Author
            preg_match('/(?:Author|Photographer)\s*:?\s*<[^>]*>([^<]+)</i', $imgHtml, $authorMatch);
            $author = trim(strip_tags($authorMatch[1] ?? 'BioLib contributor'));

            // License / rights
            preg_match('/(?:Rights|Licence|License)\s*:?\s*<[^>]*>([^<]+)</i', $imgHtml, $licenseMatch);
            $license = trim(strip_tags($licenseMatch[1] ?? ''));

            if (! $this->isAcceptableImage($license ?: null)) {
                continue;
            }

            $images[] = [
                'download_url' => $src,
                'source_url'   => "https://www.biolib.cz/en/image/id{$imgId}/",
                'license'      => $license ?: null,
                'license_url'  => null,
                'author'       => $author,
                'title'        => $name,
            ];
        }

        return $images;
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
            $this->line("    HTTP {$res->status()} ← {$url}");
            return null;
        }

        return $res->json();
    }

    private function getHtml(string $url, array $params = []): ?string
    {
        $req = Http::withUserAgent(self::USER_AGENT)->timeout(20);
        $res = $params ? $req->get($url, $params) : $req->get($url);

        if ($res->status() === 404) {
            return null;
        }

        if (! $res->successful()) {
            $this->line("    HTTP {$res->status()} ← {$url}");
            return null;
        }

        return $res->body();
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

    /**
     * Accept any image that doesn't carry an explicit "all rights reserved" notice.
     * Null/empty license (unknown) is treated as potentially free — included.
     * Explicitly reserved images are excluded.
     */
    private function isAcceptableImage(?string $license): bool
    {
        if ($license === null || $license === '') {
            return true;
        }
        $l = strtolower($license);
        return ! str_contains($l, 'all rights reserved')
            && ! str_contains($l, 'no reuse')
            && ! str_contains($l, 'rights reserved');
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
            'cc0'          => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'cc-by'        => 'https://creativecommons.org/licenses/by/4.0/',
            'cc-by-sa'     => 'https://creativecommons.org/licenses/by-sa/4.0/',
            'cc-by-nd'     => 'https://creativecommons.org/licenses/by-nd/4.0/',
            'cc-by-nc'     => 'https://creativecommons.org/licenses/by-nc/4.0/',
            'cc-by-nc-sa'  => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
            'cc-by-nc-nd'  => 'https://creativecommons.org/licenses/by-nc-nd/4.0/',
            default        => null,
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
