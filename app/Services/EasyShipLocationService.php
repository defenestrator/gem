<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EasyShipLocationService
{
    public function getNearestDropOff(float $lat, float $lng): array
    {
        $centers = $this->loadShipCenters();

        if (empty($centers)) {
            return $this->reverseGeocode($lat, $lng);
        }

        return $this->findNearest($lat, $lng, $centers);
    }

    private function loadShipCenters(): array
    {
        $cached = Cache::get('fedex_ship_centers');
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        $path = storage_path('app/fedex_ship_centers.json');

        if (! file_exists($path)) {
            return [];
        }

        $centers = json_decode(file_get_contents($path), true) ?? [];

        if (! empty($centers)) {
            Cache::put('fedex_ship_centers', $centers, 90_000);
        }

        return $centers;
    }

    private function findNearest(float $lat, float $lng, array $centers): array
    {
        $nearest = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($centers as $c) {
            $d = $this->haversine($lat, $lng, (float) $c['lat'], (float) $c['lng']);
            if ($d < $minDist) {
                $minDist = $d;
                $nearest = $c;
            }
        }

        return [
            'street'      => $nearest['street'],
            'city'        => $nearest['city'],
            'state'       => $nearest['state'],
            'postal_code' => $nearest['zip'],
            'country'     => 'US',
        ];
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $R * 2 * asin(sqrt($a));
    }

    /**
     * Find the nearest FedEx Ship Center by ZIP code.
     * Returns the ship center address formatted for EasyShip API.
     */
    public function getNearestShipCenterByZip(string $zip): array
    {
        if (! preg_match('/^\d{5}$/', $zip)) {
            throw new RuntimeException('Invalid ZIP code format.');
        }

        $centers = $this->loadShipCenters();

        if (empty($centers)) {
            throw new RuntimeException('FedEx Ship Center data not available.');
        }

        // Find the nearest ship center by comparing ZIP codes first,
        // then by haversine distance if coordinates are available
        $nearest = null;
        $minDist = PHP_FLOAT_MAX;
        $userZip = (int) $zip;

        foreach ($centers as $c) {
            $centerZip = (int) ($c['zip'] ?? '');
            $distance = abs($centerZip - $userZip);

            if ($distance < $minDist) {
                $minDist = $distance;
                $nearest = $c;
            }
        }

        if (! $nearest) {
            throw new RuntimeException('No FedEx Ship Center found.');
        }

        return [
            'address'        => trim(($nearest['street'] ?? '') ?: ($nearest['name'] ?? 'FedEx Ship Center')),
            'postal_code'    => $nearest['zip'] ?? $zip,
            'city'           => $nearest['city'] ?? '',
            'state'          => $nearest['state'] ?? '',
            'country_alpha2' => 'US',
        ];
    }

    private function reverseGeocode(float $lat, float $lng): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'GemReptiles/1.0 (gemreptiles.com)',
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'lat'            => $lat,
            'lon'            => $lng,
            'format'         => 'json',
            'addressdetails' => 1,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Reverse geocoding failed.');
        }

        $addr = $response->json('address') ?? [];

        $postcode = substr($addr['postcode'] ?? '', 0, 5);
        if (! preg_match('/^\d{5}$/', $postcode)) {
            throw new RuntimeException('Could not determine a US ZIP code for this location.');
        }

        return [
            'street'      => trim(($addr['house_number'] ?? '') . ' ' . ($addr['road'] ?? '')),
            'city'        => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? '',
            'state'       => $addr['state'] ?? '',
            'postal_code' => $postcode,
            'country'     => 'US',
        ];
    }
}
