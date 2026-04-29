<?php

use App\Services\FedExRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    Cache::flush();
    Http::preventStrayRequests();
});

// ── Token management ────────────────────────────────────────────────────────

test('fetches access token and caches it on first call', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(['access_token' => 'fresh_token', 'expires_in' => 3600], 200),
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    app(FedExRateService::class)->getRates('90210');

    expect(Cache::get('fedex_access_token'))->toBe('fresh_token');
});

test('token cached with 60-second buffer before expiry', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    app(FedExRateService::class)->getRates('90210');

    // Cache TTL is expires_in - 60 = 3540s; key must be present now
    expect(Cache::has('fedex_access_token'))->toBeTrue();
});

test('cached token is reused without a second token request', function () {
    Cache::put('fedex_access_token', 'cached_token', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    app(FedExRateService::class)->getRates('90210');

    // Only the rate request should be sent — not the token endpoint
    Http::assertSentCount(1);
});

test('throws RuntimeException on auth failure', function () {
    Http::fake([
        '*/oauth/token' => Http::response(['error' => 'unauthorized'], 401),
    ]);

    expect(fn () => app(FedExRateService::class)->getRates('90210'))
        ->toThrow(RuntimeException::class, 'FedEx authentication failed');
});

// ── Rate fetching & caching ─────────────────────────────────────────────────

test('rate results are cached for one hour', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    $service = app(FedExRateService::class);
    $service->getRates('90210');
    $service->getRates('90210'); // should hit cache

    // Token request once + rate request once = 2 total (second call fully cached)
    Http::assertSentCount(2);
});

test('different ZIP codes get separate cache entries', function () {
    Http::fake([
        '*/oauth/token'          => Http::response(['access_token' => 'tok', 'expires_in' => 3600], 200),
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    $service = app(FedExRateService::class);
    $service->getRates('90210');
    $service->getRates('10001');

    // Token once (cached after first), rates twice (different ZIPs = different cache keys)
    Http::assertSentCount(3);
});

test('throws RuntimeException on rate API failure', function () {
    Cache::put('fedex_access_token', 'tok', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response([], 500),
    ]);

    expect(fn () => app(FedExRateService::class)->getRates('90210'))
        ->toThrow(RuntimeException::class);
});

// ── Rate parsing ────────────────────────────────────────────────────────────

test('only configured service types are returned', function () {
    Cache::put('fedex_access_token', 'tok', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response([
            'output' => [
                'rateReplyDetails' => [
                    [
                        'serviceType'          => 'PRIORITY_OVERNIGHT',
                        'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 89.50]],
                    ],
                    [
                        'serviceType'          => 'GROUND_HOME_DELIVERY',
                        'ratedShipmentDetails' => [['rateType' => 'LIST', 'totalNetCharge' => 12.00]],
                    ],
                ],
            ],
        ], 200),
    ]);

    $rates = app(FedExRateService::class)->getRates('90210');

    expect($rates)->toHaveCount(1)
        ->and($rates[0]['service'])->toBe('PRIORITY_OVERNIGHT');
});

test('only LIST rate type is used', function () {
    Cache::put('fedex_access_token', 'tok', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response([
            'output' => [
                'rateReplyDetails' => [[
                    'serviceType' => 'PRIORITY_OVERNIGHT',
                    'ratedShipmentDetails' => [
                        ['rateType' => 'ACCOUNT', 'totalNetCharge' => 55.00],
                        ['rateType' => 'LIST',    'totalNetCharge' => 89.50],
                    ],
                ]],
            ],
        ], 200),
    ]);

    $rates = app(FedExRateService::class)->getRates('90210');

    expect($rates[0]['price'])->toBe('89.50');
});

test('returned rates are sorted cheapest first', function () {
    Cache::put('fedex_access_token', 'tok', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response(fakeFedExRates(), 200),
    ]);

    $rates = app(FedExRateService::class)->getRates('90210');

    $prices = array_column($rates, 'price');
    expect($prices)->toBe(['34.10', '61.25', '89.50']);
});

test('empty rate reply returns empty array', function () {
    Cache::put('fedex_access_token', 'tok', 3500);

    Http::fake([
        '*/rate/v1/rates/quotes' => Http::response(['output' => ['rateReplyDetails' => []]], 200),
    ]);

    $rates = app(FedExRateService::class)->getRates('90210');

    expect($rates)->toBeEmpty();
});

// ── Helper ───────────────────────────────────────────────────────────────────

function fakeFedExRates(): array
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
