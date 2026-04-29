<?php

use App\Services\EasyShipRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();
});

function easyshipRateResponse(): array
{
    return [
        'rates' => [
            [
                'courier_service' => ['courier_id' => 'fedex-priority-overnight', 'name' => 'FedEx Priority Overnight'],
                'courier_id'      => 'fedex-priority-overnight',
                'courier_name'    => 'FedEx Priority Overnight',
                'total_charge'    => 89.50,
                'currency'        => 'USD',
            ],
            [
                'courier_service' => ['courier_id' => 'fedex-standard-overnight', 'name' => 'FedEx Standard Overnight'],
                'courier_id'      => 'fedex-standard-overnight',
                'courier_name'    => 'FedEx Standard Overnight',
                'total_charge'    => 61.25,
                'currency'        => 'USD',
            ],
            [
                'courier_service' => ['courier_id' => 'ups-ground', 'name' => 'UPS Ground'],
                'courier_id'      => 'ups-ground',
                'courier_name'    => 'UPS Ground',
                'total_charge'    => 12.00,
                'currency'        => 'USD',
            ],
        ],
    ];
}

test('returns only configured services', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    $rates = app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA');

    expect($rates)->toHaveCount(2)
        ->and(collect($rates)->pluck('label')->all())->not->toContain('UPS Ground');
});

test('rates are sorted cheapest first', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    $prices = collect(app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA'))->pluck('price')->all();

    expect($prices)->toBe(['61.25', '89.50']);
});

test('rate shape has service label and price', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    $rate = app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA')[0];

    expect($rate)->toHaveKeys(['service', 'label', 'price']);
});

test('results are cached for one hour', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    $svc = app(EasyShipRateService::class);
    $svc->getRates('90210', 'Beverly Hills', 'CA');
    $svc->getRates('90210', 'Beverly Hills', 'CA');

    Http::assertSentCount(1);
});

test('different ZIP codes get separate cache entries', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    $svc = app(EasyShipRateService::class);
    $svc->getRates('90210', 'Beverly Hills', 'CA');
    $svc->getRates('10001', 'New York', 'NY');

    Http::assertSentCount(2);
});

test('API error throws RuntimeException', function () {
    Http::fake(['*/rates' => Http::response([], 500)]);

    expect(fn () => app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA'))
        ->toThrow(RuntimeException::class);
});

test('no matching services returns empty array', function () {
    Http::fake([
        '*/rates' => Http::response([
            'rates' => [
                ['courier_id' => 'ups-ground', 'courier_name' => 'UPS Ground', 'total_charge' => 12.00],
            ],
        ], 200),
    ]);

    expect(app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA'))->toBeEmpty();
});

test('sends correct package dimensions and weight', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA');

    Http::assertSent(function ($request) {
        $parcel = $request->data()['parcels'][0] ?? [];
        $box    = $parcel['box'] ?? [];
        $item   = $parcel['items'][0] ?? [];
        return $parcel['total_actual_weight'] === 0.91
            && $box['length'] === 20.32
            && $box['width'] === 20.32
            && $box['height'] === 15.24
            && $item['hs_code'] === '010690';
    });
});

test('sends non-residential destination', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA');

    Http::assertSent(function ($request) {
        return ($request->data()['destination_address']['is_residential'] ?? true) === false;
    });
});

test('sends origin city and state', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA');

    Http::assertSent(function ($request) {
        $origin = $request->data()['origin_address'] ?? [];
        return $origin['address'] === config('easyship.origin.address')
            && $origin['city'] === config('easyship.origin.city')
            && $origin['state'] === config('easyship.origin.state');
    });
});

test('resolves city and state from ship centers when not provided', function () {
    Http::fake(['*/rates' => Http::response(easyshipRateResponse(), 200)]);

    file_put_contents(storage_path('app/fedex_ship_centers.json'), json_encode([
        ['name' => 'FedEx Ship Center', 'street' => '123 Main St', 'city' => 'Beverly Hills', 'state' => 'CA', 'zip' => '90210', 'lat' => 34.07, 'lng' => -118.40],
    ]));

    $shipCenter = [
        'address'        => '123 Main St',
        'postal_code'    => '90210',
        'city'           => 'Beverly Hills',
        'state'          => 'CA',
        'country_alpha2' => 'US',
    ];

    app(EasyShipRateService::class)->getRates('90210', 'Beverly Hills', 'CA', $shipCenter);

    Http::assertSent(function ($request) {
        $dest = $request->data()['destination_address'] ?? [];
        return $dest['city'] === 'Beverly Hills'
            && $dest['state'] === 'CA'
            && $dest['address'] === '123 Main St';
    });

    unlink(storage_path('app/fedex_ship_centers.json'));
});

test('throws when city and state cannot be resolved', function () {
    $path = storage_path('app/fedex_ship_centers.json');
    if (file_exists($path)) unlink($path);

    expect(fn () => app(EasyShipRateService::class)->getRates('99999'))
        ->toThrow(RuntimeException::class);
});
