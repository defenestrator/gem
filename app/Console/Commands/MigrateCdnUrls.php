<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateCdnUrls extends Command
{
    protected $signature = 'media:migrate-cdn-urls
        {--dry-run : Preview affected rows without updating}';

    protected $description = 'Replace legacy DO Spaces URLs with CDN endpoint in the media table';

    private const OLD = 'https://gemx.sfo3.digitaloceanspaces.com/';
    private const NEW = 'https://assets.gemreptiles.com/';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        if ($dry) {
            $this->info('[DRY RUN — no changes will be saved]');
        }

        $this->migrateColumn('media', 'url', $dry);
        $this->migrateColumn('media', 'source_url', $dry);

        return self::SUCCESS;
    }

    private function migrateColumn(string $table, string $column, bool $dry): void
    {
        $count = DB::table($table)
            ->where($column, 'like', self::OLD . '%')
            ->count();

        if ($count === 0) {
            $this->line("  {$table}.{$column}: no rows matched");
            return;
        }

        if ($dry) {
            $samples = DB::table($table)
                ->where($column, 'like', self::OLD . '%')
                ->limit(5)
                ->pluck($column);

            $this->info("  {$table}.{$column}: {$count} row(s) would be updated");
            foreach ($samples as $url) {
                $this->line('    ' . $url);
                $this->line('    → ' . str_replace(self::OLD, self::NEW, $url));
            }
            return;
        }

        $updated = DB::table($table)
            ->where($column, 'like', self::OLD . '%')
            ->update([
                $column => DB::raw(
                    "replace({$column}, " .
                    DB::connection()->getPdo()->quote(self::OLD) . ', ' .
                    DB::connection()->getPdo()->quote(self::NEW) . ')'
                ),
            ]);

        $this->info("  {$table}.{$column}: {$updated} row(s) updated");
    }
}
