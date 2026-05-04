<?php

namespace App\Console\Commands;

use App\Models\Species;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSpecies extends Command
{
    protected $signature = 'species:import
                            {--csv= : Path to CSV file (defaults to database/schema/reptile_checklist_2020_12.csv)}
                            {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Import species from the Reptile Database CSV into the species table';

    public function handle(): int
    {
        $csvPath = $this->option('csv')
            ? $this->option('csv')
            : database_path('schema/reptile_checklist_2020_12.csv');

        $isDryRun = (bool) $this->option('dry-run');

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");
            return self::FAILURE;
        }

        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $this->error("Cannot open CSV file: {$csvPath}");
            return self::FAILURE;
        }

        // Read header row
        $headers = fgetcsv($handle);

        if ($headers === false) {
            $this->error('CSV file is empty or unreadable.');
            fclose($handle);
            return self::FAILURE;
        }

        $headers = array_map('trim', $headers);

        $imported = 0;
        $skipped  = 0;
        $errors   = 0;

        if ($isDryRun) {
            $this->warn('[DRY RUN] No data will be written.');
        }

        // Collect existing species_numbers in one query to avoid N+1 lookups
        $existing = DB::table('species')->pluck('species_number', 'species_number');

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);

            if ($data === false) {
                $errors++;
                continue;
            }

            $speciesNumber = trim($data['sp#'] ?? '');

            if ($speciesNumber === '') {
                $errors++;
                continue;
            }

            if ($existing->has($speciesNumber)) {
                $skipped++;
                continue;
            }

            if (! $isDryRun) {
                Species::query()->create([
                    'species_number' => $speciesNumber,
                    'type_species'   => trim($data['type_species']) ?: null,
                    'species'        => trim($data['Species']),
                    'author'         => trim($data['Author']) ?: null,
                    'subspecies'     => trim($data['Subspecies']) ?: null,
                    'common_name'    => trim($data['Common_name']) ?: null,
                    'higher_taxa'    => trim($data['Familyetc']) ?: null,
                    'changes'        => trim($data['changes']) ?: null,
                ]);

                // Track newly inserted to prevent duplicates within the same run
                $existing->put($speciesNumber, $speciesNumber);
            }

            $imported++;
        }

        fclose($handle);

        $label = $isDryRun ? 'Would import' : 'Imported';

        $this->table(
            ['Action', 'Count'],
            [
                [$label,   $imported],
                ['Skipped (already exist)', $skipped],
                ['Errors (malformed rows)', $errors],
            ]
        );

        if (! $isDryRun) {
            $this->info("Done. {$imported} new records added to species table.");
        } else {
            $this->info("Dry run complete. Run without --dry-run to apply.");
        }

        return self::SUCCESS;
    }
}
