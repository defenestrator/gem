<?php

namespace App\Jobs;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateSpeciesBiographyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    private const USER_AGENT = 'GemReptiles/1.0 (contact: jeremyblc@gmail.com)';

    public function __construct(
        public readonly string $modelClass,
        public readonly int    $recordId,
        public readonly bool   $force = false,
    ) {
        $this->onQueue('species-bios');
    }

    public function handle(): void
    {
        $record = $this->modelClass::find($this->recordId);
        if (! $record) {
            return;
        }

        if (! $this->force && ! empty($record->description)) {
            return;
        }

        $name     = $this->modelClass === Species::class
            ? $record->species
            : $record->full_name;
        $taxonomy = $this->buildTaxonomy($record);

        $sources = $this->gatherSources($name);

        if (empty(array_filter($sources))) {
            Log::warning("biography: no source material found for {$name}");
            return;
        }

        $bio = $this->generateBiography($name, $taxonomy, $sources);

        if (empty($bio)) {
            Log::warning("biography: empty response from API for {$name}");
            return;
        }

        $record->update(['description' => $bio]);
        Log::info("biography: saved for {$name}", ['chars' => strlen($bio)]);
    }

    // ── Source gathering ─────────────────────────────────────────────────────

    private function gatherSources(string $name): array
    {
        return [
            'wikipedia'   => $this->fromWikipedia($name),
            'inaturalist' => $this->fromINaturalist($name),
            'gbif'        => $this->fromGbif($name),
        ];
    }

    private function fromWikipedia(string $name): ?string
    {
        $res = $this->get('https://en.wikipedia.org/w/api.php', [
            'action'      => 'query',
            'prop'        => 'extracts',
            'titles'      => $name,
            'exlimit'     => 1,
            'explaintext' => true,
            'format'      => 'json',
        ]);

        if (! $res) {
            return null;
        }

        $pages = $res['query']['pages'] ?? [];
        $page  = reset($pages);

        if (! $page || isset($page['missing'])) {
            return null;
        }

        $text = trim($page['extract'] ?? '');
        return $text ?: null;
    }

    private function fromINaturalist(string $name): ?string
    {
        $res = $this->get('https://api.inaturalist.org/v1/taxa', [
            'q'        => $name,
            'rank'     => 'species,subspecies',
            'per_page' => 1,
        ]);

        if (! $res) {
            return null;
        }

        $taxon = $res['results'][0] ?? null;
        if (! $taxon || strtolower($taxon['name'] ?? '') !== strtolower($name)) {
            return null;
        }

        $parts = array_filter([
            $taxon['wikipedia_summary'] ?? null,
        ]);

        return implode("\n\n", $parts) ?: null;
    }

    private function fromGbif(string $name): ?string
    {
        $match = $this->get('https://api.gbif.org/v1/species/match', [
            'name'   => $name,
            'strict' => 'false',
        ]);

        if (! $match || ($match['matchType'] ?? '') === 'NONE' || ($match['confidence'] ?? 0) < 85) {
            return null;
        }

        $key     = $match['usageKey'] ?? null;
        $kingdom = $match['kingdom'] ?? null;
        $phylum  = $match['phylum'] ?? null;
        $class   = $match['class'] ?? null;
        $order   = $match['order'] ?? null;
        $family  = $match['family'] ?? null;
        $genus   = $match['genus'] ?? null;
        $status  = $match['status'] ?? null;

        $lines = array_filter([
            $kingdom ? "Kingdom: {$kingdom}" : null,
            $phylum  ? "Phylum: {$phylum}"   : null,
            $class   ? "Class: {$class}"     : null,
            $order   ? "Order: {$order}"     : null,
            $family  ? "Family: {$family}"   : null,
            $genus   ? "Genus: {$genus}"     : null,
            $status  ? "Taxonomic status: {$status}" : null,
        ]);

        if (! $key) {
            return implode("\n", $lines) ?: null;
        }

        // Vernacular names
        $vernacular = $this->get("https://api.gbif.org/v1/species/{$key}/vernacularNames", ['limit' => 10]);
        if ($vernacular) {
            $names = array_unique(array_filter(array_map(
                fn ($v) => ($v['language'] ?? '') === 'eng' ? ($v['vernacularName'] ?? null) : null,
                $vernacular['results'] ?? []
            )));
            if ($names) {
                $lines[] = 'Common names: ' . implode(', ', array_slice($names, 0, 5));
            }
        }

        return implode("\n", $lines) ?: null;
    }

    // ── Biography generation ─────────────────────────────────────────────────

    private function generateBiography(string $name, array $taxonomy, array $sources): ?string
    {
        $key   = config('services.anthropic.key');
        $model = config('services.anthropic.model', 'claude-haiku-4-5-20251001');

        if (! $key) {
            Log::error('biography: ANTHROPIC_API_KEY not set');
            return null;
        }

        $sourceText = '';
        if (! empty($sources['wikipedia'])) {
            $sourceText .= "## Wikipedia\n" . mb_substr($sources['wikipedia'], 0, 8000) . "\n\n";
        }
        if (! empty($sources['inaturalist'])) {
            $sourceText .= "## iNaturalist\n" . $sources['inaturalist'] . "\n\n";
        }
        if (! empty($sources['gbif'])) {
            $sourceText .= "## GBIF Taxonomy\n" . $sources['gbif'] . "\n\n";
        }

        $wordTarget = empty($sources['wikipedia']) ? '300–600' : '600–2000';

        $prompt = <<<PROMPT
Write a species profile for *{$name}* to appear on a reptile hobbyist database.

Taxonomy:
{$taxonomy}

Source material:
{$sourceText}

Requirements:
- {$wordTarget} words
- Structured prose (not bullet lists), suitable for a public species page
- Cover: taxonomy and classification, physical description, native range and habitat, behaviour and ecology, diet, reproduction, conservation status if known, and captive husbandry notes if relevant
- Write only from the source material provided; do not invent facts
- Do not include headings or markdown formatting
- Do not begin with the species name as a heading
PROMPT;

        $res = Http::withHeaders([
            'x-api-key'         => $key,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(240)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 4096,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (! $res->successful()) {
            Log::error('biography: Anthropic API error', ['status' => $res->status(), 'body' => $res->body()]);
            return null;
        }

        return trim($res->json('content.0.text') ?? '');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function buildTaxonomy(mixed $record): string
    {
        if ($record instanceof Species) {
            return implode("\n", array_filter([
                "Scientific name: {$record->species}",
                $record->common_name  ? "Common name: {$record->common_name}"   : null,
                $record->higher_taxa  ? "Higher taxa: {$record->higher_taxa}"   : null,
                $record->author       ? "Author: {$record->author}"             : null,
            ]));
        }

        return implode("\n", array_filter([
            "Scientific name: {$record->full_name}",
            "Genus: {$record->genus}",
            "Species: {$record->species}",
            "Subspecies: {$record->subspecies}",
            $record->author ? "Author: {$record->author}" : null,
        ]));
    }

    private function get(string $url, array $params = []): ?array
    {
        try {
            $res = Http::withUserAgent(self::USER_AGENT)->timeout(20)->get($url, $params);
            return $res->successful() ? $res->json() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function backoff(): array
    {
        return [60, 180, 600];
    }
}
