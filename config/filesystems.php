<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'private_s3' => [
            'driver'                  => 's3',
            'key'                     => env('PRIVATE_S3_KEY'),
            'secret'                  => env('PRIVATE_S3_SECRET'),
            'region'                  => env('PRIVATE_S3_REGION', 'sfo3'),
            'bucket'                  => env('PRIVATE_S3_BUCKET', 'privates'),
            'url'                     => env('PRIVATE_S3_URL'),
            'endpoint'                => env('PRIVATE_S3_ENDPOINT', 'https://sfo3.digitaloceanspaces.com'),
            'use_path_style_endpoint' => false,
            'visibility'              => 'private',
            'throw'                   => true,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'sfo3'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT', 'https://sfo3.digitaloceanspaces.com'),
            'use_path_style_endpoint' => false,
            'visibility'              => 'public',
            'throw'                   => false,
            'cache' => [
                'store'  => 'redis',
                'password' => env('REDIS_PASSWORD'),
                'expire' => 3.154e+7,
                'prefix' => 'gemx_media_cache',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
