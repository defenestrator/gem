<?php

namespace App\Console\Commands;

use App\Models\Animal;
use App\Models\Species;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillAnimalSpecies extends Command
{
    protected $signature = 'animals:backfill-species
                            {--dry-run : Preview matches without writing to the database}
                            {--force-first : When multiple species match a category, assign the first result}';

    protected $description = 'Backfill species_id on animals by matching the category field to species common names';

    /**
     * Explicit category → species.id overrides for ambiguous or mismatched auto-matches.
     * Keyed by exact Animal.category value.
     */
    protected array $overrides = [
        'Western Hognose'        => 5514,  // Heterodon nasicus
        'Coastal Carpet Pythons' => 7469,  // Morelia spilota
    ];

    public function handle(): int
    {
        $isDryRun    = (bool) $this->option('dry-run');
        $forceFirst  = (bool) $this->option('force-first');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No data will be written.');
        }

        $categories = Animal::query()
            ->whereNotNull('category')
            ->when(! $isDryRun, fn ($q) => $q->whereNull('species_id'))
            ->distinct()
            ->pluck('category');

        if ($categories->isEmpty()) {
            $this->info('No animals with unresolved categories found.');
            return self::SUCCESS;
        }

        $matched    = 0;
        $ambiguous  = 0;
        $unmatched  = 0;
        $rows       = [];

        foreach ($categories as $category) {
            // Explicit override takes priority over auto-match
            if (isset($this->overrides[$category])) {
                $species = Species::find($this->overrides[$category]);
            } else {
                $singular = Str::singular($category);
                $term     = strtolower($singular);

                $hits = Species::query()
                    ->whereRaw('LOWER(common_name) LIKE ?', ["%{$term}%"])
                    ->get(['id', 'species', 'common_name']);

                if ($hits->isEmpty()) {
                    $rows[]    = ['category' => $category, 'status' => 'UNMATCHED', 'species' => '—', 'count' => 0];
                    $unmatched++;
                    continue;
                }

                if ($hits->count() > 1 && ! $forceFirst) {
                    $names  = $hits->map(fn ($s) => "{$s->species} ({$s->id})")->join(', ');
                    $rows[] = ['category' => $category, 'status' => 'AMBIGUOUS', 'species' => $hits->count() . ' matches: ' . $names, 'count' => 0];
                    $ambiguous++;
                    continue;
                }

                $species = $hits->first();
            }

            $species ??= null;
            if (! $species) {
                $rows[]    = ['category' => $category, 'status' => 'UNMATCHED', 'species' => '—', 'count' => 0];
                $unmatched++;
                continue;
            }
            $animalCount = Animal::query()
                ->where('category', $category)
                ->whereNull('species_id')
                ->count();

            $rows[] = [
                'category' => $category,
                'status'   => $isDryRun ? 'WOULD ASSIGN' : 'ASSIGNED',
                'species'  => "{$species->species} (#{$species->id})",
                'count'    => $animalCount,
            ];

            if (! $isDryRun) {
                Animal::query()
                    ->where('category', $category)
                    ->whereNull('species_id')
                    ->update(['species_id' => $species->id]);
            }

            $matched++;
        }

        $this->table(
            ['Category', 'Status', 'Matched Species', 'Animals Updated'],
            collect($rows)->map(fn ($r) => [$r['category'], $r['status'], $r['species'], $r['count']])
        );

        $this->newLine();
        $this->line("Matched:   {$matched}");
        $this->line("Ambiguous: {$ambiguous}" . ($ambiguous ? ' (re-run with --force-first to assign first match)' : ''));
        $this->line("Unmatched: {$unmatched}");

        if ($isDryRun) {
            $this->info('Dry run complete. Run without --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}
