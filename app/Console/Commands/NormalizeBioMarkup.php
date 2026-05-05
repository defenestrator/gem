<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Models\Subspecies;
use Illuminate\Console\Command;

class NormalizeBioMarkup extends Command
{
    protected $signature = 'species:normalize-bios
        {--dry-run : Show changes without saving}
        {--model=all : species | subspecies | all}';

    protected $description = 'Convert Wikipedia == heading == markup to Markdown ## headings in stored biographies';

    public function handle(): int
    {
        $dry     = (bool) $this->option('dry-run');
        $model   = $this->option('model');
        $updated = 0;
        $skipped = 0;

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        $process = function (string $modelClass) use ($dry, &$updated, &$skipped) {
            $modelClass::whereNotNull('description')
                ->where('description', '!=', '')
                ->chunkById(200, function ($rows) use ($modelClass, $dry, &$updated, &$skipped) {
                    foreach ($rows as $record) {
                        $converted = $this->convert($record->description);

                        if ($converted === $record->description) {
                            $skipped++;
                            continue;
                        }

                        if ($dry) {
                            $name = $record->species ?? $record->full_name;
                            $this->line("  <info>{$name}</info>");
                            $this->line('  Before: ' . mb_substr($record->description, 0, 120));
                            $this->line('  After:  ' . mb_substr($converted, 0, 120));
                            $this->newLine();
                        } else {
                            $record->update(['description' => $converted]);
                        }

                        $updated++;
                    }
                });
        };

        if (in_array($model, ['species', 'all']))    $process(Species::class);
        if (in_array($model, ['subspecies', 'all'])) $process(Subspecies::class);

        $verb = $dry ? 'Would update' : 'Updated';
        $this->info("{$verb}: {$updated}, Already clean: {$skipped}");

        return self::SUCCESS;
    }

    private function convert(string $text): string
    {
        // ==== Heading ==== → #### Heading  (must run before === and ==)
        $text = preg_replace('/^====\s*(.+?)\s*====\s*$/m', '#### $1', $text);

        // === Heading === → ### Heading
        $text = preg_replace('/^===\s*(.+?)\s*===\s*$/m', '### $1', $text);

        // == Heading == → ## Heading
        $text = preg_replace('/^==\s*(.+?)\s*==\s*$/m', '## $1', $text);

        // Wikipedia bold/italic markup
        $text = preg_replace("/'''(.+?)'''/s", '**$1**', $text);
        $text = preg_replace("/''(.+?)''/s",   '*$1*',   $text);

        // Strip [[File:...]] and [[Image:...]] embeds entirely
        $text = preg_replace('/\[\[(File|Image):[^\]]+\]\]/i', '', $text);

        // [[link|display]] → display
        $text = preg_replace('/\[\[[^\]|]+\|([^\]]+)\]\]/', '$1', $text);

        // [[link]] → link
        $text = preg_replace('/\[\[([^\]]+)\]\]/', '$1', $text);

        // {{template|...}} → strip entirely
        $text = preg_replace('/\{\{[^}]+\}\}/', '', $text);

        // Collapse 3+ blank lines to 2
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
