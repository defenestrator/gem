<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncSpeciesTaxonomy extends Command
{
    protected $signature = 'species:sync-taxonomy
        {--dry-run         : Show changes without saving}
        {--model=all       : species | subspecies | all}
        {--limit=0         : Max records to check (0 = all)}
        {--min-confidence=97 : Minimum GBIF match confidence (0–100)}
        {--force           : Re-check records already flagged as synonyms}
        {--family=         : Restrict to one family (matches higher_taxa)}
        {--genus=          : Restrict to one genus}';

    protected $description = 'Cross-check species/subspecies names against GBIF and flag synonyms, fill missing common names and authorship';

    private const USER_AGENT = 'GemReptiles/1.0 (contact: jeremyblc@gmail.com)';
    private const GBIF_BASE  = 'https://api.gbif.org/v1';
    private const SYNONYM_TAG = 'GBIF-SYNONYM';

    private int $checked  = 0;
    private int $synonyms = 0;
    private int $filled   = 0;
    private int $skipped  = 0;

    public function handle(): int
    {
        $dry     = (bool) $this->option('dry-run');
        $model   = $this->option('model');
        $limit   = (int) $this->option('limit');
        $minConf = (int) $this->option('min-confidence');
        $force   = (bool) $this->option('force');

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        $this->line("GBIF confidence threshold: {$minConf}%");
        $this->newLine();

        if (in_array($model, ['species', 'all']))    $this->process(Species::class,    $limit, $minConf, $dry, $force);
        if (in_array($model, ['subspecies', 'all'])) $this->process(Subspecies::class, $limit, $minConf, $dry, $force);

        $verb = $dry ? 'Would change' : 'Changed';
        $this->newLine();
        $this->info("Checked: {$this->checked} | Synonyms flagged: {$this->synonyms} | Fields filled: {$this->filled} | Skipped (low confidence/no match): {$this->skipped}");

        return self::SUCCESS;
    }

    private function process(string $modelClass, int $limit, int $minConf, bool $dry, bool $force): void
    {
        $label = $modelClass === Species::class ? 'species' : 'subspecies';
        $this->line("<info>── {$label} ──────────────────────────────────────────</info>");

        $query = $modelClass::query();

        if ($this->option('genus')) {
            $genus = $this->option('genus');
            if ($modelClass === Species::class) {
                $query->where('species', 'like', $genus . ' %');
            } else {
                $query->where('genus', $genus);
            }
        }

        if ($this->option('family') && $modelClass === Species::class) {
            $query->where('higher_taxa', 'like', '%' . $this->option('family') . '%');
        }

        $isSpecies = $modelClass === Species::class;

        if (! $force && $isSpecies) {
            $query->where(function ($q) {
                $q->whereNull('changes')
                  ->orWhere('changes', 'not like', '%' . self::SYNONYM_TAG . '%');
            });
        }

        $count = 0;

        $query->orderBy('id')->chunkById(100, function ($rows) use ($modelClass, $isSpecies, $limit, $minConf, $dry, $force, &$count) {
            foreach ($rows as $record) {
                if ($limit > 0 && $count >= $limit) {
                    return false;
                }

                $name = $modelClass === Species::class ? $record->species : $record->full_name;
                $this->processRecord($record, $name, $modelClass, $isSpecies, $minConf, $dry);
                $count++;
                $this->checked++;

                // Be courteous to GBIF — 2 req/s is well within their limits
                usleep(50_000);
            }
        });
    }

    private function processRecord(mixed $record, string $name, string $modelClass, bool $isSpecies, int $minConf, bool $dry): void
    {
        $match = $this->gbifMatch($name);

        if (! $match || ($match['matchType'] ?? 'NONE') === 'NONE') {
            $this->skipped++;
            return;
        }

        $confidence = (int) ($match['confidence'] ?? 0);
        if ($confidence < $minConf) {
            $this->skipped++;
            return;
        }

        $changes = [];

        // ── Synonym detection (species only — subspecies has no changes column) ─
        if ($isSpecies && ($match['status'] ?? '') === 'SYNONYM') {
            $acceptedName = $match['species'] ?? ($match['canonicalName'] ?? null);
            if ($acceptedName && $acceptedName !== $name) {
                $tag      = self::SYNONYM_TAG . ':' . date('Y-m-d') . ':' . $acceptedName;
                $existing = $record->changes ?? '';

                if (! str_contains($existing, self::SYNONYM_TAG . ':')) {
                    $changes['changes'] = trim(($existing ? $existing . '; ' : '') . $tag);
                    $this->line("  <comment>[SYNONYM]</comment> {$name} → {$acceptedName} (confidence {$confidence}%)");
                    $this->synonyms++;
                }
            }
        } elseif (! $isSpecies && ($match['status'] ?? '') === 'SYNONYM') {
            $acceptedName = $match['species'] ?? ($match['canonicalName'] ?? null);
            if ($acceptedName && $acceptedName !== $name) {
                $this->line("  <comment>[SYNONYM/subspecies]</comment> {$name} → {$acceptedName} (confidence {$confidence}%) [log only]");
                Log::info('taxonomy-sync: subspecies synonym', ['name' => $name, 'accepted' => $acceptedName]);
                $this->synonyms++;
            }
        }

        // ── Fill empty common_name from GBIF vernacular (species only) ─────────
        if ($isSpecies && empty($record->common_name)) {
            $vernacular = $this->gbifVernacular((int) ($match['usageKey'] ?? 0));
            if ($vernacular) {
                $changes['common_name'] = $vernacular;
                $this->line("  <info>[COMMON NAME]</info> {$name} → \"{$vernacular}\"");
                $this->filled++;
            }
        }

        // ── Fill empty author from GBIF authorship ─────────────────────────────
        if (empty($record->author)) {
            $authorship = $this->gbifAuthorship((int) ($match['usageKey'] ?? 0));
            if ($authorship) {
                $changes['author'] = $authorship;
                $this->line("  <info>[AUTHOR]</info> {$name} → \"{$authorship}\"");
                $this->filled++;
            }
        }

        if (empty($changes)) {
            return;
        }

        if (! $dry) {
            $record->update($changes);
            Log::info('taxonomy-sync: updated ' . $name, $changes);
        }
    }

    // ── GBIF helpers ───────────────────────────────────────────────────────────

    private function gbifMatch(string $name): ?array
    {
        return $this->get(self::GBIF_BASE . '/species/match', [
            'name'    => $name,
            'kingdom' => 'Animalia',
            'rank'    => 'SPECIES',
            'strict'  => 'false',
        ]);
    }

    private function gbifVernacular(int $usageKey): ?string
    {
        if (! $usageKey) {
            return null;
        }

        $res = $this->get(self::GBIF_BASE . "/species/{$usageKey}/vernacularNames", [
            'limit' => 20,
        ]);

        $results = $res['results'] ?? [];

        // Prefer English, fall back to any
        foreach ($results as $v) {
            if (($v['language'] ?? '') === 'eng' && ! empty($v['vernacularName'])) {
                return $v['vernacularName'];
            }
        }

        return null;
    }

    private function gbifAuthorship(int $usageKey): ?string
    {
        if (! $usageKey) {
            return null;
        }

        $detail = $this->get(self::GBIF_BASE . "/species/{$usageKey}");
        $auth   = trim($detail['authorship'] ?? '');

        return $auth ?: null;
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
}
