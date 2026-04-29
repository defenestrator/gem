<?php

return [
    'api_key' => env('EASYSHIP_API_KEY'),

    'origin' => [
        'address'        => env('EASYSHIP_ORIGIN_ADDRESS', '2700 W Fred Smith St'),
        'postal_code'    => env('EASYSHIP_ORIGIN_ZIP', '83642'),
        'country_alpha2' => 'US',
        'city'           => env('EASYSHIP_ORIGIN_CITY', 'Meridian'),
        'state'          => env('EASYSHIP_ORIGIN_STATE', 'ID'),
    ],

    // 2 lbs = 0.91 kg | 8×8×6 in = 20.32×20.32×15.24 cm
    'package' => [
        'weight_kg' => (float) env('EASYSHIP_WEIGHT_KG', 0.91),
        'length_cm' => (float) env('EASYSHIP_LENGTH_CM', 20.32),
        'width_cm'  => (float) env('EASYSHIP_WIDTH_CM', 20.32),
        'height_cm' => (float) env('EASYSHIP_HEIGHT_CM', 15.24),
    ],

    // Exact courier_name values returned by EasyShip — update if names differ in your account
    'services' => [
        'FedEx Priority Overnight',
        'FedEx Standard Overnight',
    ],
];
