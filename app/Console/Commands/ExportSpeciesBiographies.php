<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportSpeciesBiographies extends Command
{
    protected $signature = 'species:export-bios
        {--output=bios-export.json : Output filename in storage/app/}
        {--model=all               : species | subspecies | all}';

    protected $description = 'Export species/subspecies biographies to JSON (keyed by scientific name)';

    public function handle(): int
    {
        $model  = $this->option('model');
        $output = $this->option('output');
        $rows   = [];

        if (in_array($model, ['species', 'all'])) {
            Species::whereNotNull('description')
                ->where('description', '!=', '')
                ->select('species', 'description')
                ->orderBy('species')
                ->chunkById(500, function ($chunk) use (&$rows) {
                    foreach ($chunk as $s) {
                        $rows[] = [
                            'type'        => 'species',
                            'name'        => $s->species,
                            'description' => $s->description,
                        ];
                    }
                });
        }

        if (in_array($model, ['subspecies', 'all'])) {
            Subspecies::whereNotNull('description')
                ->where('description', '!=', '')
                ->select('genus', 'species', 'subspecies', 'description')
                ->orderBy('genus')->orderBy('species')->orderBy('subspecies')
                ->chunkById(500, function ($chunk) use (&$rows) {
                    foreach ($chunk as $s) {
                        $rows[] = [
                            'type'        => 'subspecies',
                            'name'        => "{$s->genus} {$s->species} {$s->subspecies}",
                            'description' => $s->description,
                        ];
                    }
                });
        }

        if (empty($rows)) {
            $this->warn('No biographies found to export.');
            return self::SUCCESS;
        }

        Storage::put($output, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $path = storage_path("app/{$output}");

        $this->info(count($rows) . " record(s) exported → {$path}");

        return self::SUCCESS;
    }
}
