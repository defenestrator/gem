<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportChecklistData extends Command
{
    protected $signature = 'species:import-checklist
        {file             : Path to the Reptile Database checklist .xlsx file}
        {--dry-run        : Show counts without writing}
        {--task=all       : species|changes|type_species|subspecies|all}';

    protected $description = 'Import species, taxonomy changes, type_species flags, and subspecies from the Reptile Database checklist XLSX';

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;

    /** @var array<string,string> order → higher_taxa tail */
    private array $orderMap = [
        'Sauria'          => 'Sauria, Squamata (lizards)',
        'Serpentes'       => 'Serpentes, Squamata (snakes)',
        'Testudines'      => 'Testudines',
        'Crocodylia'      => 'Crocodylia',
        'Rhynchocephalia' => 'Rhynchocephalia',
    ];

    public function handle(): int
    {
        $file = $this->argument('file');
        $dry  = (bool) $this->option('dry-run');
        $task = $this->option('task');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        $rows = $this->loadChecklist($file);
        if ($rows === null) {
            return self::FAILURE;
        }

        $this->info('Loaded ' . count($rows) . ' checklist rows.');

        // Build sp_id → DB species map once
        $dbBySpId = Species::whereNotNull('species_number')
            ->select('id', 'species_number', 'species', 'type_species', 'changes', 'higher_taxa')
            ->get()
            ->keyBy(fn($s) => (string)(int)$s->species_number);

        if (in_array($task, ['species', 'all'])) {
            $this->importNewSpecies($rows, $dbBySpId, $dry);
        }

        if (in_array($task, ['changes', 'all'])) {
            $this->updateChanges($rows, $dbBySpId, $dry);
        }

        if (in_array($task, ['type_species', 'all'])) {
            $this->updateTypeSpecies($rows, $dbBySpId, $dry);
        }

        if (in_array($task, ['subspecies', 'all'])) {
            // Reload after any new species were inserted above
            $dbBySpId = Species::whereNotNull('species_number')
                ->select('id', 'species_number', 'species', 'type_species', 'changes', 'higher_taxa')
                ->get()
                ->keyBy(fn($s) => (string)(int)$s->species_number);

            $this->importSubspecies($rows, $dbBySpId, $dry);
        }

        $verb = $dry ? 'Would' : 'Done';
        $this->info("{$verb} — inserted: {$this->inserted}, updated: {$this->updated}, skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    private function loadChecklist(string $file): ?array
    {
        // Use PHP's ZipArchive to read the xlsx (no external deps)
        // xlsx is a zip; shared strings in xl/sharedStrings.xml, sheet in xl/worksheets/sheet1.xml
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            $this->error('Cannot open xlsx file.');
            return null;
        }

        // Parse shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $xml = simplexml_load_string($ssXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    // Simple string
                    $sharedStrings[] = (string)$si->t;
                } else {
                    // Rich text — collect all <r><t> nodes
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // Find the "checklist Jan 2026" sheet — get sheet index from workbook
        $sheetIndex = 1; // default
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if ($wbXml) {
            $wb = simplexml_load_string($wbXml);
            $wb->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $idx = 1;
            foreach ($wb->sheets->sheet as $sheet) {
                if (stripos((string)$sheet['name'], 'checklist') !== false) {
                    $sheetIndex = $idx;
                    break;
                }
                $idx++;
            }
        }

        $sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml");
        $zip->close();

        if (! $sheetXml) {
            $this->error('Cannot read sheet from xlsx.');
            return null;
        }

        $xml = simplexml_load_string($sheetXml);
        $xml->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        // Column map: header row defines order
        // Expected: type_species(0), Species(1), Author(2), Subspecies(3), order(4), Family(5), change(6), sp_id(7)
        $rows    = [];
        $headers = null;

        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $colLetter = preg_replace('/[0-9]/', '', (string)$cell['r']);
                $colIdx    = $this->colToIndex($colLetter);
                $type      = (string)$cell['t'];
                $value     = (string)$cell->v;

                if ($type === 's') {
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string)$cell->is->t;
                }

                $cells[$colIdx] = $value === '' ? null : $value;
            }

            if ($headers === null) {
                $headers = $cells;
                continue;
            }

            // Map by column index positionally (headers match expected order)
            $rows[] = [
                'type_species' => $cells[0] ?? null,
                'species'      => $cells[1] ?? null,
                'author'       => $cells[2] ?? null,
                'subspecies'   => $cells[3] ?? null,
                'order'        => $cells[4] ?? null,
                'family'       => $cells[5] ?? null,
                'change'       => $cells[6] ?? null,
                'sp_id'        => isset($cells[7]) ? (string)(int)$cells[7] : null,
            ];
        }

        return $rows;
    }

    private function colToIndex(string $col): int
    {
        $col   = strtoupper($col);
        $index = 0;
        foreach (str_split($col) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function importNewSpecies(array $rows, \Illuminate\Support\Collection $dbBySpId, bool $dry): void
    {
        $this->line('Importing new species…');

        foreach ($rows as $row) {
            $spId = $row['sp_id'];
            if (! $spId || ! $row['species']) {
                $this->skipped++;
                continue;
            }

            if ($dbBySpId->has($spId)) {
                // Already exists — skip (changes/type_species handled by other tasks)
                continue;
            }

            $higherTaxa = $this->buildHigherTaxa($row['order'], $row['family']);

            $this->inserted++;

            if ($dry) {
                $this->line("  [NEW] {$row['species']} (sp_id={$spId}) — {$higherTaxa}");
                continue;
            }

            Species::create([
                'species_number' => $spId,
                'species'        => $row['species'],
                'author'         => $row['author'],
                'higher_taxa'    => $higherTaxa,
                'type_species'   => $row['type_species'] ?: null,
                'changes'        => $row['change'] ?: null,
            ]);
        }

        $this->line("  New species: {$this->inserted} would be inserted.");
    }

    private function updateChanges(array $rows, \Illuminate\Support\Collection $dbBySpId, bool $dry): void
    {
        $this->line('Updating taxonomy changes…');
        $count = 0;

        foreach ($rows as $row) {
            if (! $row['change'] || ! $row['sp_id']) {
                continue;
            }

            $existing = $dbBySpId->get($row['sp_id']);
            if (! $existing) {
                continue;
            }

            $newChange = trim($row['change']);

            // Append if already has a value; replace if null
            $currentChanges = $existing->changes;
            if ($currentChanges) {
                if (str_contains($currentChanges, $newChange)) {
                    continue; // already noted
                }
                $merged = $currentChanges . "\n" . $newChange;
            } else {
                $merged = $newChange;
            }

            $count++;
            $this->updated++;

            if ($dry) {
                $this->line("  [CHANGE] {$existing->species}: {$merged}");
                continue;
            }

            Species::where('id', $existing->id)->update(['changes' => $merged]);
        }

        $this->line("  Changes updated: {$count}");
    }

    private function updateTypeSpecies(array $rows, \Illuminate\Support\Collection $dbBySpId, bool $dry): void
    {
        $this->line('Updating type_species flags…');
        $count = 0;

        foreach ($rows as $row) {
            if (! $row['type_species'] || ! $row['sp_id']) {
                continue;
            }

            $existing = $dbBySpId->get($row['sp_id']);
            if (! $existing) {
                continue;
            }

            $newVal = trim($row['type_species']);
            $rawVal = $existing->getRawOriginal('type_species');

            if ($rawVal === $newVal) {
                continue;
            }

            $count++;
            $this->updated++;

            if ($dry) {
                $this->line("  [TYPE] {$existing->species}: '{$rawVal}' → '{$newVal}'");
                continue;
            }

            Species::where('id', $existing->id)->update(['type_species' => $newVal]);
        }

        $this->line("  type_species updated: {$count}");
    }

    private function importSubspecies(array $rows, \Illuminate\Support\Collection $dbBySpId, bool $dry): void
    {
        $this->line('Importing missing subspecies…');

        // Build existing subspecies set keyed by "genus|species|subspecies"
        $existing = Subspecies::select('genus', 'species', 'subspecies', 'species_id')
            ->get()
            ->keyBy(fn($s) => strtolower("{$s->genus}|{$s->species}|{$s->subspecies}"));

        $newCount = 0;

        foreach ($rows as $row) {
            if (! $row['subspecies'] || ! $row['sp_id'] || ! $row['species']) {
                continue;
            }

            $parentSpecies = $dbBySpId->get($row['sp_id']);
            $speciesId     = $parentSpecies?->id;

            $lines = array_filter(array_map('trim', explode("\n", $row['subspecies'])));

            foreach ($lines as $line) {
                $parts = explode(' ', $line);
                if (count($parts) < 3) {
                    continue;
                }

                $genus   = $parts[0];
                $species = $parts[1];
                $subsp   = $parts[2];
                $author  = count($parts) > 3 ? implode(' ', array_slice($parts, 3)) : null;

                $key = strtolower("{$genus}|{$species}|{$subsp}");

                if ($existing->has($key)) {
                    continue;
                }

                if ($speciesId === null) {
                    $this->skipped++;
                    continue;
                }

                $newCount++;
                $this->inserted++;

                if ($dry) {
                    $this->line("  [NEW SUBSP] {$genus} {$species} {$subsp} {$author}");
                    continue;
                }

                Subspecies::create([
                    'species_id' => $speciesId,
                    'genus'      => $genus,
                    'species'    => $species,
                    'subspecies' => $subsp,
                    'author'     => $author,
                ]);
            }
        }

        $this->line("  New subspecies: {$newCount}");
    }

    private function buildHigherTaxa(?string $order, ?string $family): ?string
    {
        $tail = $this->orderMap[$order] ?? $order;

        if ($family && $tail) {
            return "{$family}, {$tail}";
        }

        return $tail ?? $family;
    }
}
