<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessAnimalMedia extends Command
{
    protected $signature = 'media:process-animals
        {--dry-run       : Count records without writing anything}
        {--force         : Re-process images that already have thumbnail_url set}
        {--no-sync       : Skip S3 sync steps; process from local storage only}
        {--skip-optimize : Skip JPEG recompression; generate thumbnails only}
        {--batch=50      : Records per chunk}';

    protected $description = 'Sync animal images from DO Spaces, optimize originals, generate 400×400 thumbnails, and link them to media records';

    private const SPACES_HOST  = 'gemx.sfo3.digitaloceanspaces.com';
    private const ENDPOINT     = 'https://sfo3.digitaloceanspaces.com';
    private const BUCKET       = 'gemx';
    private const THUMB_PREFIX = 'thumbs/';
    private const THUMB_SIZE   = 400;
    private const JPEG_QUALITY = 85;

    private string $localBase;

    public function handle(): int
    {
        $dryRun       = (bool) $this->option('dry-run');
        $force        = (bool) $this->option('force');
        $noSync       = (bool) $this->option('no-sync');
        $skipOptimize = (bool) $this->option('skip-optimize');
        $batchSize    = max(1, (int) $this->option('batch'));

        $this->localBase = storage_path('app/public/spaces');

        if ($dryRun) {
            $this->warn('[DRY RUN] No files will be written.');
        }

        if (! $noSync && ! $dryRun) {
            if (! $this->syncDown()) {
                return self::FAILURE;
            }
        }

        $query = Media::query()
            ->where('mediable_type', 'App\Models\Animal')
            ->whereNotNull('url')
            ->where('url', 'like', '%' . self::SPACES_HOST . '%');

        if (! $force) {
            $query->whereNull('thumbnail_url');
        }

        $total = $query->count();
        $this->info("Found {$total} animal image(s) to process.");

        if ($dryRun || $total === 0) {
            if (! $dryRun && ! $noSync) {
                return $this->syncUp() ? self::SUCCESS : self::FAILURE;
            }
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok   = 0;
        $fail = 0;

        $query->chunkById($batchSize, function ($records) use ($bar, $skipOptimize, &$ok, &$fail) {
            foreach ($records as $media) {
                try {
                    $this->processOne($media, $skipOptimize);
                    $ok++;
                } catch (\Throwable $e) {
                    $this->newLine();
                    $this->warn("  Failed [{$media->id}]: {$e->getMessage()}");
                    $fail++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Processed: {$ok}" . ($fail ? ", failed: {$fail}" : '') . '.');

        if (! $noSync) {
            if (! $this->syncUp()) {
                return self::FAILURE;
            }
        }

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function processOne(Media $media, bool $skipOptimize): void
    {
        [$localPath, $s3Key] = $this->resolve($media->url);

        if (! file_exists($localPath)) {
            if (! Storage::disk('s3')->exists($s3Key)) {
                $media->delete();
                throw new \RuntimeException("S3 object missing — orphaned media record {$media->id} deleted");
            }
            throw new \RuntimeException("Local file missing after sync: {$localPath}");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($localPath);
        if (! str_starts_with($mime ?? '', 'image/')) {
            @unlink($localPath);
            Storage::disk('s3')->delete($s3Key);
            $media->delete();
            throw new \RuntimeException("Non-image content ({$mime}) — S3 object and media record deleted");
        }

        $filename   = pathinfo($s3Key, PATHINFO_FILENAME) . '.jpg';
        $thumbS3Key = self::THUMB_PREFIX . 'animals/' . $media->mediable_id . '/' . $filename;
        $thumbLocal = "{$this->localBase}/{$thumbS3Key}";
        $thumbDir   = dirname($thumbLocal);

        if (! is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $img = Image::make($localPath);
        $img->fit(self::THUMB_SIZE, self::THUMB_SIZE);
        $img->save($thumbLocal, self::JPEG_QUALITY);
        $img->destroy();

        if (! $skipOptimize) {
            $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $orig = Image::make($localPath);
                $orig->save($localPath, self::JPEG_QUALITY);
                $orig->destroy();
            }
        }

        $media->thumbnail_url = 'https://' . self::SPACES_HOST . '/' . $thumbS3Key;
        $media->save();
    }

    private function resolve(string $url): array
    {
        $s3Key = ltrim(parse_url($url, PHP_URL_PATH), '/');
        $local = "{$this->localBase}/{$s3Key}";
        return [$local, $s3Key];
    }

    private function syncDown(): bool
    {
        $target = $this->localBase . '/animals/';

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $this->info("Syncing s3://" . self::BUCKET . "/animals/ → {$target}");

        $result = Process::env($this->awsEnv())
            ->timeout(0)
            ->run(
                'aws s3 sync s3://' . self::BUCKET . '/animals/ ' . escapeshellarg($target)
                . ' --endpoint-url=' . self::ENDPOINT,
                fn ($type, $out) => $this->getOutput()->write($out),
            );

        if (! $result->successful()) {
            $this->error('Sync down failed: ' . $result->errorOutput());
            return false;
        }

        $this->info('Sync down complete.');
        return true;
    }

    private function syncUp(): bool
    {
        $cacheImmutable = 'public, max-age=31536000, immutable';

        $this->info('Syncing optimized originals back to DO Spaces...');
        $r1 = Process::env($this->awsEnv())
            ->timeout(0)
            ->run(
                'aws s3 sync ' . escapeshellarg($this->localBase . '/animals/')
                . ' s3://' . self::BUCKET . '/animals/'
                . ' --endpoint-url=' . self::ENDPOINT
                . ' --acl public-read'
                . ' --cache-control ' . escapeshellarg($cacheImmutable),
                fn ($type, $out) => $this->getOutput()->write($out),
            );

        if (! $r1->successful()) {
            $this->error('Sync up (originals) failed: ' . $r1->errorOutput());
            return false;
        }

        $thumbDir = $this->localBase . '/' . self::THUMB_PREFIX . 'animals/';
        if (is_dir($thumbDir)) {
            $this->info('Syncing thumbnails to DO Spaces...');
            $r2 = Process::env($this->awsEnv())
                ->timeout(0)
                ->run(
                    'aws s3 sync ' . escapeshellarg($thumbDir)
                    . ' s3://' . self::BUCKET . '/' . self::THUMB_PREFIX . 'animals/'
                    . ' --endpoint-url=' . self::ENDPOINT
                    . ' --acl public-read'
                    . ' --cache-control ' . escapeshellarg($cacheImmutable),
                    fn ($type, $out) => $this->getOutput()->write($out),
                );

            if (! $r2->successful()) {
                $this->error('Sync up (thumbnails) failed: ' . $r2->errorOutput());
                return false;
            }
        }

        $this->info('Sync up complete.');
        return true;
    }

    private function awsEnv(): array
    {
        return [
            'AWS_ACCESS_KEY_ID'     => config('filesystems.disks.s3.key'),
            'AWS_SECRET_ACCESS_KEY' => config('filesystems.disks.s3.secret'),
            'AWS_DEFAULT_REGION'    => config('filesystems.disks.s3.region', 'sfo3'),
        ];
    }
}
