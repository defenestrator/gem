<?php

use App\Services\EasyShipLocationService;
use Illuminate\Support\Facades\Http;

$shipCenters = [
    [
        'name'   => 'FedEx Ship Center',
        'street' => '500 NE Multnomah St',
        'city'   => 'Portland',
        'state'  => 'OR',
        'zip'    => '97209',
        'lat'    => 45.5271,
        'lng'    => -122.6594,
    ],
    [
        'name'   => 'FedEx Ship Center',
        'street' => '100 Commercial St SE',
        'city'   => 'Salem',
        'state'  => 'OR',
        'zip'    => '97301',
        'lat'    => 44.9430,
        'lng'    => -123.0351,
    ],
    [
        'name'   => 'FedEx Ship Center',
        'street' => '2700 W Fred Smith St',
        'city'   => 'Meridian',
        'state'  => 'ID',
        'zip'    => '83642',
        'lat'    => 43.6150,
        'lng'    => -116.3915,
    ],
];

beforeEach(function () use ($shipCenters) {
    Http::preventStrayRequests();
    file_put_contents(storage_path('app/fedex_ship_centers.json'), json_encode($shipCenters));
});

afterEach(function () {
    $path = storage_path('app/fedex_ship_centers.json');
    if (file_exists($path)) {
        unlink($path);
    }
});

test('getNearestShipCenterByZip finds exact match', function () {
    $service = app(EasyShipLocationService::class);
    $result = $service->getNearestShipCenterByZip('97209');

    expect($result)
        ->toHaveKeys(['address', 'postal_code', 'city', 'state', 'country_alpha2'])
        ->and($result['postal_code'])->toBe('97209')
        ->and($result['city'])->toBe('Portland')
        ->and($result['state'])->toBe('OR')
        ->and($result['address'])->toBe('500 NE Multnomah St');
});

test('getNearestShipCenterByZip works with Meridian ID location', function () {
    $service = app(EasyShipLocationService::class);
    $result = $service->getNearestShipCenterByZip('83642');

    expect($result)
        ->and($result['postal_code'])->toBe('83642')
        ->and($result['city'])->toBe('Meridian')
        ->and($result['state'])->toBe('ID')
        ->and($result['address'])->toBe('2700 W Fred Smith St');
});

test('getNearestShipCenterByZip throws on invalid ZIP format', function () {
    $service = app(EasyShipLocationService::class);

    expect(fn () => $service->getNearestShipCenterByZip('12345wrong'))
        ->toThrow(RuntimeException::class);
});

test('getNearestShipCenterByZip throws when no ship centers available', function () {
    unlink(storage_path('app/fedex_ship_centers.json'));

    $service = app(EasyShipLocationService::class);

    expect(fn () => $service->getNearestShipCenterByZip('97209'))
        ->toThrow(RuntimeException::class, 'FedEx Ship Center data not available');
});

test('getNearestShipCenterByZip returns address field formatted correctly', function () {
    $service = app(EasyShipLocationService::class);
    $result = $service->getNearestShipCenterByZip('97209');

    expect($result['address'])->not->toBeEmpty()
        ->and($result['address'])->toBe('500 NE Multnomah St');
});

test('shipping quote controller uses nearest ship center', function () {
    Http::fake(['*/rates' => Http::response([
        'rates' => [
            [
                'courier_service' => ['courier_id' => 'fedex-priority-overnight', 'name' => 'FedEx Priority Overnight'],
                'courier_id'      => 'fedex-priority-overnight',
                'courier_name'    => 'FedEx Priority Overnight',
                'total_charge'    => 89.50,
                'currency'        => 'USD',
            ],
        ],
    ], 200)]);

    $this->postJson('/shipping/quote', ['zip_code' => '97209'])
        ->assertOk()
        ->assertJsonStructure([
            'rates' => [
                '*' => ['service', 'label', 'price'],
            ],
            'ship_center' => ['address', 'postal_code', 'city', 'state', 'country_alpha2'],
        ])
        ->assertJsonPath('ship_center.postal_code', '97209')
        ->assertJsonPath('ship_center.city', 'Portland');
});
