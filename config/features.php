<?php

return [
    'classifieds' => env('FEATURE_CLASSIFIEDS', env('APP_ENV', 'production') !== 'production'),
];
