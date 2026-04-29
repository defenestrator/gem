<?php

return [
    'classifieds' => env('FEATURE_CLASSIFIEDS', env('APP_ENV', 'production') !== 'production'),
    'easyship'    => (bool) env('FEATURE_EASYSHIP', false),
];
