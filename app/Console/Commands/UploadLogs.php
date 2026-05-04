<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UploadLogs extends Command
{
    protected $signature = 'logs:upload
        {--dry-run : List files that would be uploaded without uploading}';

    protected $description = 'Upload application logs from storage/logs/ to the private S3 bucket';

    public function handle(): int
    {
        $logDir  = storage_path('logs');
        $files   = glob("{$logDir}/*.log") ?: [];
        $host    = gethostname() ?: 'unknown-host';
        $date    = now()->format('Y-m');
        $dry     = (bool) $this->option('dry-run');
        $uploaded = 0;
        $failed   = 0;

        if (empty($files)) {
            $this->info('No log files found.');
            return self::SUCCESS;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('private_s3');

        foreach ($files as $localPath) {
            $filename   = basename($localPath);
            $remotePath = "logs/{$host}/{$date}/{$filename}";

            if ($dry) {
                $this->line("  [dry-run] {$localPath} → {$remotePath}");
                $uploaded++;
                continue;
            }

            try {
                $stream = fopen($localPath, 'r');
                $disk->put($remotePath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $this->line("  Uploaded: {$remotePath}");
                $uploaded++;
            } catch (\Throwable $e) {
                $this->warn("  Failed {$filename}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. Uploaded: {$uploaded}, Failed: {$failed}");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
