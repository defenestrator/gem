<?php

namespace App\Console\Commands;

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
        // ── 2. Species search Redis cache ────────────────────────────────────
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

        $animalImages = Media::where('moderation_status', 'approved')
            ->where('mediable_type', 'App\Models\Animal')
            ->pluck('url')
            ->all();

        return array_merge($thumbs, $animalImages);
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
