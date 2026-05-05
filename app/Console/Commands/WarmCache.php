<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WarmCache extends Command
{
    protected $signature = 'app:warm
        {--base-url= : Override the base URL to hit (defaults to APP_URL)}';

    protected $description = 'Warm application caches after deploy';

    private const TAXA = [
        'lizards',
        'snakes',
        'geckos',
        'turtles',
        'amphisbaenia',
        'crocodilians',
    ];

    public function handle(): int
    {
        $base = rtrim($this->option('base-url') ?: config('app.url'), '/');
        $searchUrl = $base . '/species/search';

        $this->info("Warming cache against {$base}");
        $this->newLine();

        // Config / route / view cache
        $this->call('optimize');
        $this->newLine();

        // Species browse — page 1 (alphabetical, no filter)
        $this->warm($searchUrl, [], 'browse p1 (all)');

        // Each taxon filter — page 1
        foreach (self::TAXA as $taxon) {
            $this->warm($searchUrl, ['taxon' => $taxon], "browse p1 ({$taxon})");
        }

        // Has-media variants for most common taxa
        $this->warm($searchUrl, ['has_media' => '1'], 'browse p1 (has media)');

        foreach (['snakes', 'lizards', 'geckos'] as $taxon) {
            $this->warm($searchUrl, ['taxon' => $taxon, 'has_media' => '1'], "browse p1 ({$taxon} + has media)");
        }

        $this->newLine();
        $this->info('Cache warm complete.');

        return self::SUCCESS;
    }

    private function warm(string $url, array $params, string $label): void
    {
        try {
            $res = Http::timeout(30)
                ->withoutVerifying()
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url, $params);

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
