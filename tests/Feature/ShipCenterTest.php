<?php

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

// ── Primary path: static Ship Center lookup ──────────────────────────────────

test('coordinates near Portland return Portland ship center', function () {
    $this->postJson('/shipping/location', ['lat' => 45.5231, 'lng' => -122.6765])
        ->assertOk()
        ->assertJsonStructure(['location' => ['street', 'city', 'state', 'postal_code', 'country']])
        ->assertJsonPath('location.postal_code', '97209')
        ->assertJsonPath('location.city', 'Portland');
});

test('coordinates near Salem return Salem ship center', function () {
    // Salem, OR: lat 44.9429, lng -123.0351 — farther from Portland center
    $this->postJson('/shipping/location', ['lat' => 44.9429, 'lng' => -123.0351])
        ->assertOk()
        ->assertJsonPath('location.postal_code', '97301')
        ->assertJsonPath('location.city', 'Salem');
});

// ── Fallback: Nominatim when no ship centers file ────────────────────────────

test('falls back to nominatim when no ship centers file', function () {
    unlink(storage_path('app/fedex_ship_centers.json'));

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'house_number' => '500',
                'road'         => 'NE Multnomah St',
                'city'         => 'Portland',
                'state'        => 'Oregon',
                'postcode'     => '97209',
                'country_code' => 'us',
            ],
        ], 200),
    ]);

    $this->postJson('/shipping/location', ['lat' => 45.5231, 'lng' => -122.6765])
        ->assertOk()
        ->assertJsonPath('location.postal_code', '97209');
});

test('ZIP+4 from nominatim fallback is normalized to 5 digits', function () {
    unlink(storage_path('app/fedex_ship_centers.json'));

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'house_number' => '500',
                'road'         => 'NE Multnomah St',
                'city'         => 'Portland',
                'state'        => 'Oregon',
                'postcode'     => '97209-1234',
                'country_code' => 'us',
            ],
        ], 200),
    ]);

    $this->postJson('/shipping/location', ['lat' => 45.5231, 'lng' => -122.6765])
        ->assertOk()
        ->assertJsonPath('location.postal_code', '97209');
});

test('nominatim failure returns 503 when no ship centers file', function () {
    unlink(storage_path('app/fedex_ship_centers.json'));

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([], 500),
    ]);

    $this->postJson('/shipping/location', ['lat' => 45.5231, 'lng' => -122.6765])
        ->assertStatus(503)
        ->assertJson(['error' => 'Could not find a nearby FedEx Ship Center.']);
});

// ── Validation ───────────────────────────────────────────────────────────────

test('missing lat returns 422', function () {
    $this->postJson('/shipping/location', ['lng' => -122.6765])->assertStatus(422);
});

test('missing lng returns 422', function () {
    $this->postJson('/shipping/location', ['lat' => 45.5231])->assertStatus(422);
});

test('non-numeric lat returns 422', function () {
    $this->postJson('/shipping/location', ['lat' => 'abc', 'lng' => -122.6765])->assertStatus(422);
});

test('lat out of range returns 422', function () {
    $this->postJson('/shipping/location', ['lat' => 91.0, 'lng' => -122.6765])->assertStatus(422);
});

test('lng out of range returns 422', function () {
    $this->postJson('/shipping/location', ['lat' => 45.5231, 'lng' => 181.0])->assertStatus(422);
});
