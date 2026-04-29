<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FedExRateService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('fedex.environment') === 'production'
            ? 'https://apis.fedex.com'
            : 'https://apis-sandbox.fedex.com';
    }

    public function getRates(string $destPostalCode): array
    {
        $cacheKey = 'fedex_rates_' . config('fedex.origin.postal_code') . '_' . $destPostalCode;

        return Cache::remember($cacheKey, 3600, function () use ($destPostalCode) {
            return $this->fetchRates($destPostalCode);
        });
    }

    private function fetchRates(string $destPostalCode): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post("{$this->baseUrl}/rate/v1/rates/quotes", [
                'accountNumber' => [
                    'value' => config('fedex.account_number'),
                ],
                'requestedShipment' => [
                    'shipper' => [
                        'address' => [
                            'postalCode'  => config('fedex.origin.postal_code'),
                            'countryCode' => config('fedex.origin.country_code'),
                        ],
                    ],
                    'recipient' => [
                        'address' => [
                            'postalCode'  => $destPostalCode,
                            'countryCode' => 'US',
                            'residential' => false,
                        ],
                    ],
                    'pickupType'      => 'DROPOFF_AT_FEDEX_LOCATION',
                    'rateRequestType' => ['LIST'],
                    'requestedPackageLineItems' => [
                        [
                            'weight' => [
                                'units' => 'LB',
                                'value' => config('fedex.package.weight_lbs'),
                            ],
                            'dimensions' => [
                                'length' => config('fedex.package.length_in'),
                                'width'  => config('fedex.package.width_in'),
                                'height' => config('fedex.package.height_in'),
                                'units'  => 'IN',
                            ],
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('FedEx rate API error: ' . $response->status());
        }

        return $this->parseRates($response->json());
    }

    private function parseRates(array $data): array
    {
        $wanted  = config('fedex.services');
        $details = $data['output']['rateReplyDetails'] ?? [];
        $rates   = [];

        foreach ($details as $detail) {
            $serviceType = $detail['serviceType'] ?? '';

            if (! isset($wanted[$serviceType])) {
                continue;
            }

            foreach ($detail['ratedShipmentDetails'] ?? [] as $ratedDetail) {
                if (($ratedDetail['rateType'] ?? '') === 'LIST') {
                    $total = $ratedDetail['totalNetCharge'] ?? null;

                    if ($total !== null) {
                        $rates[] = [
                            'service' => $serviceType,
                            'label'   => $wanted[$serviceType],
                            'price'   => number_format((float) $total, 2),
                        ];
                        break;
                    }
                }
            }
        }

        usort($rates, fn ($a, $b) => (float) $a['price'] <=> (float) $b['price']);

        return $rates;
    }

    private function accessToken(): string
    {
        if ($cached = Cache::get('fedex_access_token')) {
            return $cached;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type'    => 'client_credentials',
            'client_id'     => config('fedex.client_id'),
            'client_secret' => config('fedex.client_secret'),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('FedEx authentication failed');
        }

        $token  = $response->json('access_token');
        $expiry = $response->json('expires_in', 3600);

        Cache::put('fedex_access_token', $token, $expiry - 60);

        return $token;
    }
}
