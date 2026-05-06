<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

class ImportSpeciesTable extends Command
{
    protected $signature = 'species:import-table
        {file             : Path to JSON export file}
        {--dry-run        : Show counts without writing}
        {--model=all      : species | subspecies | all}';

    protected $description = 'Upsert species/subspecies from a JSON export file into the database';

    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;

    public function handle(): int
    {
        $file = $this->argument('file');
        $dry  = (bool) $this->option('dry-run');
        $model = $this->option('model');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error('Invalid JSON.');
            return self::FAILURE;
        }

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        $species    = array_filter($rows, fn ($r) => $r['type'] === 'species');
        $subspecies = array_filter($rows, fn ($r) => $r['type'] === 'subspecies');

        if (in_array($model, ['species', 'all'])) {
            $this->importSpecies($species, $dry);
        }

        if (in_array($model, ['subspecies', 'all'])) {
            $this->importSubspecies($subspecies, $dry);
        }

        $verb = $dry ? 'Would insert/update' : 'Done';
        $this->info("{$verb} — inserted: {$this->inserted}, updated: {$this->updated}, skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    private function importSpecies(array $rows, bool $dry): void
    {
        $this->line('Importing species…');

        foreach ($rows as $row) {
            $key = $row['species_number'] ?? null;

            if (! $key) {
                $this->skipped++;
                continue;
            }

            $existing = Species::where('species_number', $key)->first();

            $data = array_filter([
                'species'      => $row['species']      ?? null,
                'author'       => $row['author']       ?? null,
                'common_name'  => $row['common_name']  ?? null,
                'higher_taxa'  => $row['higher_taxa']  ?? null,
                'changes'      => $row['changes']      ?? null,
                'description'  => $row['description']  ?? null,
                'type_species' => $row['type_species'] ?? null,
            ], fn ($v) => $v !== null);

            if ($dry) {
                $existing ? $this->updated++ : $this->inserted++;
                continue;
            }

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                Species::create(array_merge($data, ['species_number' => $key]));
                $this->inserted++;
            }
        }
    }

    private function importSubspecies(array $rows, bool $dry): void
    {
        $this->line('Importing subspecies…');

        // Build a species name → id map to resolve parent_species
        $speciesMap = Species::pluck('id', 'species')->all();

        foreach ($rows as $row) {
            $genus     = $row['genus']     ?? null;
            $species   = $row['species']   ?? null;
            $subspecies = $row['subspecies'] ?? null;

            if (! $genus || ! $species || ! $subspecies) {
                $this->skipped++;
                continue;
            }

            $existing = Subspecies::where('genus', $genus)
                ->where('species', $species)
                ->where('subspecies', $subspecies)
                ->first();

            $parentName = $row['parent_species'] ?? null;
            $speciesId  = $parentName ? ($speciesMap[$parentName] ?? null) : null;

            $data = array_filter([
                'species_id'  => $speciesId,
                'genus'       => $genus,
                'species'     => $species,
                'subspecies'  => $subspecies,
                'author'      => $row['author']      ?? null,
                'description' => $row['description'] ?? null,
            ], fn ($v) => $v !== null);

            if ($dry) {
                $existing ? $this->updated++ : $this->inserted++;
                continue;
            }

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                Subspecies::create($data);
                $this->inserted++;
            }
        }
    }
}
