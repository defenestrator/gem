<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

class ExportSpeciesTable extends Command
{
    protected $signature = 'species:export-table
        {--output=species-export.json : Output filename written to public/}
        {--model=all                  : species | subspecies | all}';

    protected $description = 'Export full species and subspecies table data to JSON for production import';

    public function handle(): int
    {
        $model  = $this->option('model');
        $output = $this->option('output');
        $rows   = [];

        if (in_array($model, ['species', 'all'])) {
            $count = 0;
            Species::orderBy('id')->chunkById(500, function ($chunk) use (&$rows, &$count) {
                foreach ($chunk as $s) {
                    $rows[] = [
                        'type'           => 'species',
                        'species_number' => $s->species_number,
                        'species'        => $s->species,
                        'author'         => $s->author,
                        'common_name'    => $s->common_name,
                        'higher_taxa'    => $s->higher_taxa,
                        'changes'        => $s->changes,
                        'description'    => $s->description,
                        'type_species'   => $s->getRawOriginal('type_species'),
                    ];
                    $count++;
                }
            });
            $this->line("  species: {$count} rows");
        }

        if (in_array($model, ['subspecies', 'all'])) {
            $count = 0;
            Subspecies::with('parentSpecies')->orderBy('id')->chunkById(500, function ($chunk) use (&$rows, &$count) {
                foreach ($chunk as $s) {
                    $rows[] = [
                        'type'           => 'subspecies',
                        'parent_species' => $s->parentSpecies?->species,
                        'genus'          => $s->genus,
                        'species'        => $s->species,
                        'subspecies'     => $s->subspecies,
                        'author'         => $s->author,
                        'description'    => $s->description,
                    ];
                    $count++;
                }
            });
            $this->line("  subspecies: {$count} rows");
        }

        if (empty($rows)) {
            $this->warn('No rows found.');
            return self::SUCCESS;
        }

        $path = public_path($output);
        file_put_contents($path, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info(count($rows) . ' total rows → ' . $path);

        return self::SUCCESS;
    }
}
