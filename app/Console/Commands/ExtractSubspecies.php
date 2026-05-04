<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

class ExtractSubspecies extends Command
{
    protected $signature = 'subspecies:extract
                            {--dry-run : Preview without writing to the database}
                            {--force  : Re-extract species that already have subspecies rows}';

    protected $description = 'Extract subspecies rows from the species.subspecies text field';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $force    = (bool) $this->option('force');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No data will be written.');
        }

        $query = Species::query()
            ->whereNotNull('subspecies')
            ->where('subspecies', '!=', '');

        if (! $force && ! $isDryRun) {
            $alreadyDone = Subspecies::query()->distinct()->pluck('species_id');
            $query->whereNotIn('id', $alreadyDone);
        }

        $species = $query->get(['id', 'species', 'subspecies']);

        if ($species->isEmpty()) {
            $this->info('Nothing to extract. Use --force to re-extract existing records.');
            return self::SUCCESS;
        }

        $totalInserted = 0;
        $totalSkipped  = 0;
        $rows          = [];

        foreach ($species as $sp) {
            $lines    = preg_split('/\r?\n/', trim($sp->subspecies));
            $inserted = 0;
            $skipped  = 0;

            if ($force && ! $isDryRun) {
                Subspecies::where('species_id', $sp->id)->delete();
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $parts = preg_split('/\s+/', $line, 4);

                if (count($parts) < 3) {
                    $skipped++;
                    continue;
                }

                [$genus, $speciesName, $subspeciesName] = $parts;
                $author = $parts[3] ?? null;

                if (! $isDryRun) {
                    Subspecies::create([
                        'species_id' => $sp->id,
                        'genus'      => $genus,
                        'species'    => $speciesName,
                        'subspecies' => $subspeciesName,
                        'author'     => $author,
                    ]);
                }

                $inserted++;
            }

            $totalInserted += $inserted;
            $totalSkipped  += $skipped;

            $rows[] = [
                $sp->id,
                $sp->species,
                $isDryRun ? 'WOULD INSERT' : 'INSERTED',
                $inserted,
                $skipped,
            ];
        }

        $this->table(
            ['Species ID', 'Species', 'Status', 'Rows ' . ($isDryRun ? 'Would Insert' : 'Inserted'), 'Skipped'],
            $rows
        );

        $this->newLine();
        $this->line('Species processed: ' . $species->count());
        $this->line(($isDryRun ? 'Would insert' : 'Inserted') . ': ' . $totalInserted);
        $this->line('Skipped (malformed): ' . $totalSkipped);

        if ($isDryRun) {
            $this->info('Dry run complete. Run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}
