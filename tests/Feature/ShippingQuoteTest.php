<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

$shipCenters = [
    [
        'name'   => 'FedEx Ship Center',
        'street' => '2700 W Fred Smith St',
        'city'   => 'Meridian',
        'state'  => 'ID',
        'zip'    => '83642',
        'lat'    => 43.6150,
        'lng'    => -116.3915,
    ],
    [
        'name'   => 'FedEx Ship Center',
        'street' => '500 NE Multnomah St',
        'city'   => 'Portland',
        'state'  => 'OR',
        'zip'    => '97209',
        'lat'    => 45.5271,
        'lng'    => -122.6594,
    ],
];

beforeEach(function () use ($shipCenters) {
    Cache::flush();
    Http::preventStrayRequests();
    file_put_contents(storage_path('app/fedex_ship_centers.json'), json_encode($shipCenters));
});

afterEach(function () {
    $path = storage_path('app/fedex_ship_centers.json');
    if (file_exists($path)) {
        unlink($path);
    }
});

// ── Successful responses ────────────────────────────────────────────────────

test('valid ZIP with city and state returns 200 with two rate options', function () {
    Http::fake(['*/rates' => Http::response(easyshipQuoteResponse(), 200)]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'])
        ->assertOk()
        ->assertJsonCount(2, 'rates')
        ->assertJsonStructure(['rates' => [['service', 'label', 'price']]]);
});

test('rates are sorted cheapest first', function () {
    Http::fake(['*/rates' => Http::response(easyshipQuoteResponse(), 200)]);

    $rates = $this->postJson('/shipping/quote', ['zip_code' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'])
        ->assertOk()
        ->json('rates');

    expect($rates[0]['price'])->toBe('61.25')
        ->and($rates[1]['price'])->toBe('89.50');
});

test('rate labels match configured service names', function () {
    Http::fake(['*/rates' => Http::response(easyshipQuoteResponse(), 200)]);

    $labels = $this->postJson('/shipping/quote', ['zip_code' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'])
        ->assertOk()
        ->collect('rates')
        ->pluck('label')
        ->all();

    expect($labels)->toContain('FedEx Priority Overnight')
        ->and($labels)->toContain('FedEx Standard Overnight');
});

// ── Validation ──────────────────────────────────────────────────────────────

test('missing zip_code returns 422', function () {
    $this->postJson('/shipping/quote', [])->assertStatus(422);
});

test('non-numeric zip returns 422', function () {
    $this->postJson('/shipping/quote', ['zip_code' => 'ABCDE'])->assertStatus(422);
});

test('four-digit zip returns 422', function () {
    $this->postJson('/shipping/quote', ['zip_code' => '9021'])->assertStatus(422);
});

test('six-digit zip returns 422', function () {
    $this->postJson('/shipping/quote', ['zip_code' => '902101'])->assertStatus(422);
});

test('zip with dash returns 422', function () {
    $this->postJson('/shipping/quote', ['zip_code' => '90210-1234'])->assertStatus(422);
});

// ── API error handling ──────────────────────────────────────────────────────

test('EasyShip API 500 returns 503', function () {
    Http::fake(['*/rates' => Http::response([], 500)]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'])
        ->assertStatus(503)
        ->assertJson(['error' => 'Shipping quote unavailable. Please try again.']);
});

test('response with no matching services returns 422', function () {
    Http::fake([
        '*/rates' => Http::response([
            'rates' => [
                ['courier_id' => 'ups-ground', 'courier_name' => 'UPS Ground', 'total_charge' => 12.00],
            ],
        ], 200),
    ]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210', 'city' => 'Beverly Hills', 'state' => 'CA'])
        ->assertStatus(422)
        ->assertJson(['error' => 'No rates available for this ZIP code.']);
});

test('missing city and state with unknown ZIP returns 503', function () {
    $path = storage_path('app/fedex_ship_centers.json');
    $existed = file_exists($path);
    if ($existed) rename($path, $path . '.bak');

    $this->postJson('/shipping/quote', ['zip_code' => '99999'])
        ->assertStatus(503);

    if ($existed) rename($path . '.bak', $path);
});

// ── Helpers ─────────────────────────────────────────────────────────────────

function easyshipQuoteResponse(): array
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
        ],
    ];
}
