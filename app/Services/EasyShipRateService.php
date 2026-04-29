<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EasyShipRateService
{
    private const BASE_URL = 'https://public-api.easyship.com/2024-09';

    /**
     * Get shipping rates to a specific FedEx Ship Center.
     * Accepts either a full address array (from getNearestShipCenterByZip)
     * or legacy parameters for backward compatibility.
     */
    public function getRates(string $destPostalCode, string $destCity = '', string $destState = '', array $destAddress = null): array
    {
        // Use provided destination address if available, otherwise build from components
        if ($destAddress === null) {
            $destAddress = $this->resolveDestination($destPostalCode, $destCity, $destState);
        }

        $cacheKey = 'easyship_rates_' . config('easyship.origin.postal_code') . '_' . $destAddress['postal_code'];

        return Cache::remember($cacheKey, 3600, function () use ($destAddress) {
            return $this->fetchRates($destAddress);
        });
    }

    private function resolveDestination(string $zip, string $city, string $state): array
    {
        if ($city !== '' && $state !== '') {
            return [
                'address'        => '',
                'postal_code'    => $zip,
                'city'           => $city,
                'state'          => $state,
                'country_alpha2' => 'US',
            ];
        }

        $centers = Cache::get('fedex_ship_centers');

        if (! is_array($centers) || empty($centers)) {
            $path    = storage_path('app/fedex_ship_centers.json');
            $centers = file_exists($path)
                ? (json_decode(file_get_contents($path), true) ?? [])
                : [];
        }

        foreach ($centers as $c) {
            if (($c['zip'] ?? '') === $zip) {
                return [
                    'address'        => trim(($c['street'] ?? '') ?: ($c['name'] ?? 'FedEx Ship Center')),
                    'postal_code'    => $c['zip'],
                    'city'           => $c['city'],
                    'state'          => $c['state'],
                    'country_alpha2' => 'US',
                ];
            }
        }

        throw new RuntimeException('Could not resolve FedEx Ship Center for ZIP ' . $zip);
    }

    private function fetchRates(array $destAddress): array
    {
        $pkg = config('easyship.package');

        // Build destination address for API, with optional address field
        $destAddr = [
            'postal_code'    => $destAddress['postal_code'],
            'country_alpha2' => $destAddress['country_alpha2'] ?? 'US',
            'city'           => $destAddress['city'],
            'state'          => $destAddress['state'],
            'is_residential' => false,
        ];

        if (! empty($destAddress['address'])) {
            $destAddr['address'] = $destAddress['address'];
        }

        $response = Http::withToken(config('easyship.api_key'))
            ->acceptJson()
            ->post(self::BASE_URL . '/rates', [
                'origin_address' => [
                    'address'        => config('easyship.origin.address'),
                    'postal_code'    => config('easyship.origin.postal_code'),
                    'country_alpha2' => config('easyship.origin.country_alpha2'),
                    'city'           => config('easyship.origin.city'),
                    'state'          => config('easyship.origin.state'),
                ],
                'destination_address' => $destAddr,
                'incoterms' => 'DDU',
                'parcels'   => [
                    [
                        'total_actual_weight' => $pkg['weight_kg'],
                        'box'                 => [
                            'length' => $pkg['length_cm'],
                            'width'  => $pkg['width_cm'],
                            'height' => $pkg['height_cm'],
                        ],
                        'items' => [
                            [
                                'description'            => 'Live reptile',
                                'hs_code'                => '010690',
                                'quantity'               => 1,
                                'actual_weight'          => $pkg['weight_kg'],
                                'declared_currency'      => 'USD',
                                'declared_customs_value' => 100,
                            ],
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('EasyShip rate API error: ' . $response->status());
        }

        return $this->parseRates($response->json());
    }

    private function parseRates(array $data): array
    {
        $wanted = config('easyship.services');
        $rates  = [];

        foreach ($data['rates'] ?? [] as $rate) {
            $courierName = $rate['courier_name'] ?? ($rate['courier_service']['name'] ?? '');

            // Only include FedEx overnight services for FedEx Ship Center pickups
            if (! in_array($courierName, $wanted)) {
                continue;
            }

            $total = $rate['total_charge'] ?? null;

            if ($total !== null) {
                $rates[] = [
                    'service' => $rate['courier_id'] ?? $rate['courier_service']['courier_id'] ?? $courierName,
                    'label'   => $courierName,
                    'price'   => number_format((float) $total, 2),
                ];
            }
        }

        usort($rates, fn ($a, $b) => (float) $a['price'] <=> (float) $b['price']);

        return $rates;
    }
}
