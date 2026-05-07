<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FlushCdnCache extends Command
{
    protected $signature = 'cdn:flush
        {--files=* : Specific path patterns to purge (default: all)}';

    protected $description = 'Purge the DigitalOcean Spaces CDN cache';

    public function handle(): int
    {
        $token      = config('services.digitalocean.token');
        $endpointId = config('services.digitalocean.cdn_endpoint_id');

        if (! $token) {
            $this->error('DO_API_TOKEN not set.');
            return self::FAILURE;
        }

        if (! $endpointId) {
            $endpointId = $this->resolveEndpointId($token);
            if (! $endpointId) {
                $this->error('DO_CDN_ENDPOINT_ID not set and could not be resolved from API.');
                return self::FAILURE;
            }
            $this->line("Resolved CDN endpoint: {$endpointId}");
        }

        $files = $this->option('files') ?: ['*'];

        $this->info('Purging CDN cache for: ' . implode(', ', $files));

        $response = Http::withToken($token)
            ->delete("https://api.digitalocean.com/v2/cdn/endpoints/{$endpointId}/cache", [
                'files' => $files,
            ]);

        if ($response->status() === 204) {
            $this->info('CDN cache flushed.');
            return self::SUCCESS;
        }

        $this->error("CDN flush failed [{$response->status()}]: " . $response->body());
        return self::FAILURE;
    }

    private function resolveEndpointId(string $token): ?string
    {
        $response = Http::withToken($token)
            ->get('https://api.digitalocean.com/v2/cdn/endpoints');

        if (! $response->successful()) {
            return null;
        }

        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region', 'sfo3');
        $origin = "{$bucket}.{$region}.digitaloceanspaces.com";

        foreach ($response->json('endpoints', []) as $endpoint) {
            if (str_contains($endpoint['origin'] ?? '', $origin)) {
                return $endpoint['id'];
            }
        }

        return null;
    }
}
