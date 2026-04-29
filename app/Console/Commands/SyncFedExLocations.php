<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncFedExLocations extends Command
{
    protected $signature   = 'fedex:sync-locations';
    protected $description = 'Fetch FedEx Ship Center locations from OpenStreetMap and cache to storage.';

    private const OVERPASS_URL = 'https://overpass-api.de/api/interpreter';
    private const CACHE_KEY    = 'fedex_ship_centers';
    private const CACHE_TTL    = 90_000; // 25 hours — outlasts the daily schedule by 1 hr

    // Bounding box covers the 48 contiguous states + DC
    private const QUERY = '[out:json][timeout:90];node["name"~"FedEx Ship Center",i](24.396308,-124.848974,49.384358,-66.885444);out body;';

    public function handle(): int
    {
        $this->info('Querying OpenStreetMap for FedEx Ship Centers...');

        $response = Http::timeout(100)
            ->withHeaders(['User-Agent' => 'GemReptiles/1.0 (gemreptiles.com)'])
            ->asForm()
            ->post(self::OVERPASS_URL, ['data' => self::QUERY]);

        if (! $response->successful()) {
            $this->error('Overpass API request failed: ' . $response->status());
            return self::FAILURE;
        }

        $elements = $response->json('elements') ?? [];

        if (empty($elements)) {
            $this->warn('No elements returned from Overpass API.');
            return self::FAILURE;
        }

        $centers = [];

        foreach ($elements as $node) {
            $tags = $node['tags'] ?? [];
            $zip  = substr($tags['addr:postcode'] ?? '', 0, 5);

            if (! preg_match('/^\d{5}$/', $zip)) {
                continue;
            }

            $street = trim(($tags['addr:housenumber'] ?? '') . ' ' . ($tags['addr:street'] ?? ''));

            $centers[] = [
                'name'   => $tags['name'] ?? 'FedEx Ship Center',
                'street' => $street,
                'city'   => $tags['addr:city'] ?? '',
                'state'  => $tags['addr:state'] ?? '',
                'zip'    => $zip,
                'lat'    => (float) $node['lat'],
                'lng'    => (float) $node['lon'],
            ];
        }

        if (empty($centers)) {
            $this->error('No valid Ship Center records after filtering.');
            return self::FAILURE;
        }

        $path = storage_path('app/fedex_ship_centers.json');
        file_put_contents($path, json_encode($centers, JSON_PRETTY_PRINT));

        Cache::put(self::CACHE_KEY, $centers, self::CACHE_TTL);

        $this->info('Stored ' . count($centers) . ' FedEx Ship Centers to storage and cache.');

        return self::SUCCESS;
    }
}
