<?php

namespace App\Console\Commands;

use App\Models\Animal;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MirrorAnimalMedia extends Command
{
    protected $signature = 'animals:mirror-media
        {--dry-run      : Show what would be mirrored without writing anything}
        {--force        : Re-mirror images already on DO Spaces}
        {--rewrite-json : Rewrite animals.json Photo_Urls from DB after mirroring}';

    protected $description = 'Download animal images from external CDN and re-host on DO Spaces';

    private const SPACES_HOST = 'gemx.sfo3.digitaloceanspaces.com';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $query = Media::query()
            ->where('mediable_type', 'App\Models\Animal')
            ->whereNotNull('url');

        if (! $force) {
            $query->where('url', 'not like', '%' . self::SPACES_HOST . '%');
        }

        $records = $query->with('mediable')->get();

        if ($records->isEmpty()) {
            $this->info('No external animal media found.');
            return self::SUCCESS;
        }

        $this->info("Found {$records->count()} image(s) to mirror." . ($dryRun ? ' [dry-run]' : ''));
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        $ok = 0;
        $fail = 0;

        foreach ($records as $media) {
            $slug = $media->mediable?->slug ?? 'unknown';
            $ext  = strtolower(pathinfo(parse_url($media->url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
            $path = "animals/{$slug}/" . Str::uuid() . ".{$ext}";

            if ($dryRun) {
                $bar->advance();
                $ok++;
                continue;
            }

            try {
                $response = Http::withoutVerifying()->timeout(30)->get($media->url);

                if (! $response->successful()) {
                    $fail++;
                    $bar->advance();
                    continue;
                }

                Storage::disk('s3')->put($path, $response->body(), [
                    'visibility'    => 'public',
                    'CacheControl'  => 'public, max-age=31536000, immutable',
                    'ContentType'   => $response->header('Content-Type') ?? 'image/jpeg',
                ]);

                $media->url = Storage::disk('s3')->url($path);
                $media->save();

                $ok++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("  Failed [{$media->id}]: {$e->getMessage()}");
                $fail++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Mirrored: {$ok}" . ($fail ? ", failed: {$fail}" : '') . ($dryRun ? ' [dry-run]' : ''));

        if ($this->option('rewrite-json') && ! $dryRun) {
            $this->rewriteAnimalsJson();
        }

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function rewriteAnimalsJson(): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists('animals.json')) {
            $this->warn('animals.json not found — skipping Photo_Urls rewrite.');
            return;
        }

        $data = json_decode($disk->get('animals.json'), true);
        if (! is_array($data)) {
            $this->warn('animals.json invalid JSON — skipping rewrite.');
            return;
        }

        $this->info('Rewriting Photo_Urls in animals.json from mirrored DB media...');

        $mediaBySlug = Animal::query()
            ->with(['media' => fn ($q) => $q->select('id', 'mediable_id', 'mediable_type', 'url')])
            ->get(['id', 'slug'])
            ->mapWithKeys(fn ($a) => [
                $a->slug => $a->media->pluck('url')->filter()->values()->all(),
            ]);

        $updated = 0;
        foreach ($data as &$item) {
            $slug = $item['Animal_Id*'] ?? null;
            if ($slug && ! empty($mediaBySlug[$slug])) {
                $item['Photo_Urls'] = implode(' ', $mediaBySlug[$slug]);
                $updated++;
            }
        }
        unset($item);

        $disk->put('animals.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info("Rewrote Photo_Urls for {$updated} animals.");
    }
}
