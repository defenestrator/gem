<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();
});

// ── Successful responses ────────────────────────────────────────────────────

test('valid ZIP returns 200 with three rate options', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(fedexTokenResponse(), 200),
        '*/rate/v1/rates/quotes' => Http::response(fedexRateResponse(), 200),
    ]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertOk()
        ->assertJsonCount(3, 'rates')
        ->assertJsonStructure(['rates' => [['service', 'label', 'price']]]);
});

test('rates are sorted cheapest first', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(fedexTokenResponse(), 200),
        '*/rate/v1/rates/quotes' => Http::response(fedexRateResponse(), 200),
    ]);

    $rates = $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertOk()
        ->json('rates');

    expect($rates[0]['price'])->toBe('34.10')
        ->and($rates[1]['price'])->toBe('61.25')
        ->and($rates[2]['price'])->toBe('89.50');
});

test('rate labels match configured service names', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(fedexTokenResponse(), 200),
        '*/rate/v1/rates/quotes' => Http::response(fedexRateResponse(), 200),
    ]);

    $labels = $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertOk()
        ->collect('rates')
        ->pluck('label')
        ->all();

    expect($labels)->toContain('Priority Overnight')
        ->and($labels)->toContain('Standard Overnight')
        ->and($labels)->toContain('2Day');
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

test('FedEx auth failure returns 503 with message', function () {
    Http::fake([
        '*/oauth/token' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertStatus(503)
        ->assertJson(['error' => 'Shipping quote unavailable. Please try again.']);
});

test('FedEx rate API 500 returns 503', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(fedexTokenResponse(), 200),
        '*/rate/v1/rates/quotes' => Http::response([], 500),
    ]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertStatus(503);
});

test('response with no matching service types returns 422', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(fedexTokenResponse(), 200),
        '*/rate/v1/rates/quotes' => Http::response([
            'output' => [
                'rateReplyDetails' => [[
                    'serviceType'          => 'GROUND_HOME_DELIVERY',
                    'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 12.00]],
                ]],
            ],
        ], 200),
    ]);

    $this->postJson('/shipping/quote', ['zip_code' => '90210'])
        ->assertStatus(422)
        ->assertJson(['error' => 'No rates available for this ZIP code.']);
});

// ── Helpers ─────────────────────────────────────────────────────────────────

function fedexTokenResponse(): array
{
    return ['access_token' => 'test_bearer_token', 'token_type' => 'Bearer', 'expires_in' => 3600];
}

function fedexRateResponse(): array
{
    return [
        'output' => [
            'rateReplyDetails' => [
                [
                    'serviceType'          => 'PRIORITY_OVERNIGHT',
                    'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 89.50]],
                ],
                [
                    'serviceType'          => 'STANDARD_OVERNIGHT',
                    'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 61.25]],
                ],
                [
                    'serviceType'          => 'FEDEX_2_DAY',
                    'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 34.10]],
                ],
            ],
        ],
    ];
}
