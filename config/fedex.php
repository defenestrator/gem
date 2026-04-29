<?php

return [
    'client_id'      => env('FEDEX_CLIENT_ID'),
    'client_secret'  => env('FEDEX_CLIENT_SECRET'),
    'account_number' => env('FEDEX_ACCOUNT_NUMBER'),
    'environment'    => env('FEDEX_ENVIRONMENT', 'sandbox'),

    'origin' => [
        'postal_code'  => env('FEDEX_ORIGIN_ZIP', '83642'),
        'country_code' => 'US',
    ],

    'package' => [
        'weight_lbs' => (float) env('FEDEX_DEFAULT_WEIGHT_LBS', 2),
        'length_in'  => (int) env('FEDEX_DEFAULT_LENGTH_IN', 12),
        'width_in'   => (int) env('FEDEX_DEFAULT_WIDTH_IN', 8),
        'height_in'  => (int) env('FEDEX_DEFAULT_HEIGHT_IN', 6),
    ],

    'services' => [
        'PRIORITY_OVERNIGHT' => 'Priority Overnight',
        'STANDARD_OVERNIGHT' => 'Standard Overnight',
        'FEDEX_2_DAY'        => '2Day',
    ],
];
