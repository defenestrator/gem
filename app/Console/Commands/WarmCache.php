<?php

namespace App\Console\Commands;

use App\Models\Animal;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WarmCache extends Command
{
    protected $signature = 'app:warm
        {--base-url=      : Override the base URL for app routes (defaults to APP_URL)}
        {--cdn-url=       : CDN base URL for image warming (defaults to AWS_URL / storage URL)}
        {--images=thumbs  : Image warm scope: thumbs | all | none}
        {--concurrency=20 : Concurrent image HEAD requests}';

    protected $description = 'Warm application and CDN image caches after deploy';

    private const TAXA = [
        'lizards',
        'snakes',
        'geckos',
        'turtles',
        'amphisbaenia',
        'crocodilians',
    ];

    private const WELCOME_SORTS = [
        'recent',
        'price-low',
        'price-high',
        'date-new',
        'category',
        'category-desc',
    ];

    private const ANIMAL_SORTS = [
        'recent',
        'name-asc',
        'name-desc',
        'oldest',
    ];

    private const ANIMAL_AVAILABILITIES = [
        'for_sale',
        'on_hold',
        'holdback',
        'breeder',
        'sold',
        'not_for_sale',
    ];

    // Direct DO Spaces origin — what is stored in media.url
    private const SPACES_ORIGIN = 'https://gemx.sfo3.digitaloceanspaces.com/';

    public function handle(): int
    {
        $base      = rtrim($this->option('base-url') ?: config('app.url'), '/');
        $cdnBase   = rtrim($this->option('cdn-url') ?: config('filesystems.disks.s3.url', self::SPACES_ORIGIN), '/') . '/';
        $imageMode = $this->option('images');
        $concur    = max(1, (int) $this->option('concurrency'));

        $this->info("Warming cache against {$base}");
        $this->newLine();

        // ── 1. Welcome page sort variants ────────────────────────────────────
        $this->info('Welcome page cache:');
        foreach (self::WELCOME_SORTS as $sort) {
            $this->warmHtml($base . '/', ['sort' => $sort], "sort={$sort}");
        }
        $this->newLine();

        // ── 2. Animals page sort + availability variants ──────────────────────
        $this->info('Animals page cache:');
        foreach (self::ANIMAL_SORTS as $sort) {
            $this->warmHtml($base . '/animals', ['sort' => $sort], "sort={$sort}");
        }
        foreach (self::ANIMAL_AVAILABILITIES as $avail) {
            $this->warmHtml($base . '/animals', ['availability' => $avail], "availability={$avail}");
        }
        $this->newLine();

        // ── 3. Animal show pages ─────────────────────────────────────────────
        $this->info('Animal show pages:');
        $slugs = Animal::where('status', 'published')->pluck('slug');
        foreach ($slugs as $slug) {
            $this->warmHtml($base . '/animals/' . $slug, [], $slug);
        }
        $this->newLine();

        // ── 4. Species index + category pages ───────────────────────────────
        $this->info('Species + category page cache:');
        $this->warmHtml($base . '/species', [], 'species index');
        foreach ([
            '/categories',
            '/categories/ball-pythons',
            '/categories/carpet-pythons',
            '/categories/corn-snakes',
            '/categories/reticulated-pythons',
            '/categories/western-hognose',
        ] as $path) {
            $this->warmHtml($base . $path, [], $path);
        }
        $this->newLine();

        // ── 5. Species search Redis cache ────────────────────────────────────
        $this->info('Species search cache:');
        $searchUrl = $base . '/species/search';

        $this->warmRoute($searchUrl, [], 'browse p1 (all)');
        $this->warmRoute($searchUrl, ['has_media' => '1'], 'browse p1 (has media)');

        foreach (self::TAXA as $taxon) {
            $this->warmRoute($searchUrl, ['taxon' => $taxon], "browse p1 ({$taxon})");
        }

        // ── 3. CDN image cache ───────────────────────────────────────────────
        if ($imageMode === 'none') {
            $this->newLine();
            $this->info('Image warming skipped (--images=none).');
            $this->newLine();
            $this->info('Cache warm complete.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info("CDN image cache ({$imageMode}, concurrency {$concur}):");

        $urls = $this->collectImageUrls($imageMode);
        $this->line("  Collected " . count($urls) . " URLs");

        if (empty($urls)) {
            $this->warn('  No image URLs found — skipping.');
        } else {
            $this->warmImages($urls, $cdnBase, $concur);
        }

        $this->newLine();
        $this->info('Cache warm complete.');

        return self::SUCCESS;
    }

    // ── URL collection ───────────────────────────────────────────────────────

    private function collectImageUrls(string $mode): array
    {
        if ($mode === 'all') {
            return Media::where('moderation_status', 'approved')
                ->whereNotNull('url')
                ->pluck('url')
                ->all();
        }

        // thumbs — one per species/subspecies (latest approved), plus all animal images
        // DISTINCT ON is PostgreSQL-native: picks the highest id per mediable
        $thumbs = array_column(DB::select("
            SELECT DISTINCT ON (mediable_type, mediable_id) url
            FROM media
            WHERE moderation_status = 'approved'
              AND mediable_type IN ('App\\Models\\Species', 'App\\Models\\Subspecies')
            ORDER BY mediable_type, mediable_id, id DESC
        "), 'url');

        $animalMedia = Media::where('moderation_status', 'approved')
            ->where('mediable_type', 'App\Models\Animal')
            ->get(['url', 'thumbnail_url']);

        $animalUrls = $animalMedia
            ->flatMap(fn ($m) => array_filter([$m->url, $m->thumbnail_url]))
            ->unique()
            ->all();

        return array_merge($thumbs, $animalUrls);
    }

    // ── CDN image warming ────────────────────────────────────────────────────

    private function warmImages(array $urls, string $cdnBase, int $concur): void
    {
        $cdnUrls = array_map(
            fn (string $url) => str_replace(self::SPACES_ORIGIN, $cdnBase, $url),
            $urls
        );

        $chunks  = array_chunk($cdnUrls, $concur);
        $ok      = 0;
        $fail    = 0;
        $bar     = $this->output->createProgressBar(count($cdnUrls));
        $bar->start();

        foreach ($chunks as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $url) {
                    $pool->withoutVerifying()->timeout(15)->head($url);
                }
            });

            foreach ($responses as $res) {
                if (is_a($res, \Throwable::class) || (method_exists($res, 'failed') && $res->failed())) {
                    $fail++;
                } else {
                    $ok++;
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->line("  <info>✓ {$ok} warmed</info>" . ($fail ? ", <comment>{$fail} failed</comment>" : ''));
    }

    // ── App route warming ────────────────────────────────────────────────────

    private function warmHtml(string $url, array $params, string $label): void
    {
        try {
            $res = Http::timeout(30)->withoutVerifying()->get($url, $params);
            if ($res->successful()) {
                $this->line("  <info>✓</info> {$label} — HTTP {$res->status()}");
            } else {
                $this->warn("  ✗ {$label} — HTTP {$res->status()}");
            }
        } catch (\Throwable $e) {
            $this->warn("  ✗ {$label} — {$e->getMessage()}");
        }
    }

    private function warmRoute(string $url, array $params, string $label): void
    {
        try {
            $res    = Http::timeout(30)->withoutVerifying()->withHeaders(['Accept' => 'application/json'])->get($url, $params);
            $status = $res->status();
            $total  = $res->json('meta.total', '?');

            if ($res->successful()) {
                $this->line("  <info>✓</info> {$label} — {$total} results (HTTP {$status})");
            } else {
                $this->warn("  ✗ {$label} — HTTP {$status}");
            }
        } catch (\Throwable $e) {
            $this->warn("  ✗ {$label} — {$e->getMessage()}");
        }
    }
}
