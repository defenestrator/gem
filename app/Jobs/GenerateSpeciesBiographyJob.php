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
    public int $timeout = 120;

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

        $name = $this->modelClass === Species::class
            ? $record->species
            : $record->full_name;

        $bio = $this->assemble($name, $record);

        if (empty($bio)) {
            Log::info("biography: no source material for {$name}");
            return;
        }

        $record->update(['description' => $bio]);
        Log::info("biography: saved for {$name}", ['chars' => strlen($bio)]);
    }

    // ── Assembly ─────────────────────────────────────────────────────────────

    private function assemble(string $name, mixed $record): ?string
    {
        $sections = [];

        // Taxonomy block
        $sections[] = "## Taxonomy\n\n" . $this->taxonomyBlock($record);

        // Wikipedia full extract — primary prose source
        $wiki = $this->fromWikipedia($name);
        if ($wiki) {
            $sections[] = "## Overview\n\n" . $wiki;
        }

        // iNaturalist summary — fallback / supplement when Wikipedia is thin
        $inat = $this->fromINaturalist($name);
        if ($inat && (! $wiki || mb_strlen($wiki) < 500)) {
            $sections[] = "## Description\n\n" . $inat;
        }

        // GBIF classification + common names
        $gbif = $this->fromGbif($name);
        if ($gbif) {
            $sections[] = "## Classification\n\n" . $gbif;
        }

        $body = implode("\n\n", array_filter($sections));

        return $body ? $this->normalizeMarkup($body) : null;
    }

    private function normalizeMarkup(string $text): string
    {
        $text = preg_replace('/^====\s*(.+?)\s*====\s*$/m', '#### $1', $text);
        $text = preg_replace('/^===\s*(.+?)\s*===\s*$/m',   '### $1',  $text);
        $text = preg_replace('/^==\s*(.+?)\s*==\s*$/m',     '## $1',   $text);
        $text = preg_replace("/'''(.+?)'''/s", '**$1**', $text);
        $text = preg_replace("/''(.+?)''/s",   '*$1*',   $text);
        $text = preg_replace('/\[\[(File|Image):[^\]]+\]\]/i', '', $text);
        $text = preg_replace('/\[\[[^\]|]+\|([^\]]+)\]\]/', '$1', $text);
        $text = preg_replace('/\[\[([^\]]+)\]\]/', '$1', $text);
        $text = preg_replace('/\{\{[^}]+\}\}/', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    // ── Sources ───────────────────────────────────────────────────────────────

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

        $taxon = $res['results'][0] ?? null;
        if (! $taxon || strtolower($taxon['name'] ?? '') !== strtolower($name)) {
            return null;
        }

        $summary = trim($taxon['wikipedia_summary'] ?? '');
        return $summary ?: null;
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

        $lines = array_filter([
            ($match['kingdom'] ?? null) ? 'Kingdom: ' . $match['kingdom'] : null,
            ($match['phylum']  ?? null) ? 'Phylum: '  . $match['phylum']  : null,
            ($match['class']   ?? null) ? 'Class: '   . $match['class']   : null,
            ($match['order']   ?? null) ? 'Order: '   . $match['order']   : null,
            ($match['family']  ?? null) ? 'Family: '  . $match['family']  : null,
        ]);

        $key = $match['usageKey'] ?? null;
        if ($key) {
            $vernacular = $this->get("https://api.gbif.org/v1/species/{$key}/vernacularNames", ['limit' => 10]);
            $engNames   = array_unique(array_filter(array_map(
                fn ($v) => ($v['language'] ?? '') === 'eng' ? ($v['vernacularName'] ?? null) : null,
                $vernacular['results'] ?? []
            )));
            if ($engNames) {
                $lines[] = 'Common names: ' . implode(', ', array_slice($engNames, 0, 5));
            }
        }

        return $lines ? implode("\n", $lines) : null;
    }

    // ── Taxonomy block from the DB record ─────────────────────────────────────

    private function taxonomyBlock(mixed $record): string
    {
        if ($record instanceof Species) {
            return implode("\n", array_filter([
                "Scientific name: {$record->species}",
                $record->common_name ? "Common name: {$record->common_name}" : null,
                $record->higher_taxa ? "Classification: {$record->higher_taxa}" : null,
                $record->author      ? "Described by: {$record->author}" : null,
            ]));
        }

        return implode("\n", array_filter([
            "Scientific name: {$record->full_name}",
            "Genus: {$record->genus}",
            "Species: {$record->species}",
            "Subspecies: {$record->subspecies}",
            $record->author ? "Described by: {$record->author}" : null,
        ]));
    }

    // ── HTTP helper ───────────────────────────────────────────────────────────

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
        return [30, 90, 300];
    }
}
