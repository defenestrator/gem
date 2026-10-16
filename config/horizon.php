<?php

return [
    'name' => 'Gem',
    'domain' => env('HORIZON_DOMAIN'),
    'path' => 'horizon',
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', 'gem_horizon:'),
    'middleware' => ['web', 'auth'],
    'waits' => [
        'redis:default' => 300,
        'redis-short:short' => 60,
        'redis-short:mail' => 60,
        'redis-imports:imports' => 300,
        'redis-short:species-images' => 300,
        'redis-short:species-bios' => 300,
    ],
    'trim' => ['recent' => 60, 'pending' => 60, 'completed' => 60, 'recent_failed' => 10080, 'failed' => 10080, 'monitored' => 10080],
    'silenced' => [],
    'silenced_tags' => [],
    'metrics' => ['trim_snapshots' => ['job' => 24, 'queue' => 24]],
    // Wait for current jobs before Supervisor starts a replacement master.
    'fast_termination' => false,
    'memory_limit' => 64,
    'defaults' => [
        'legacy' => [
            'connection' => 'redis', 'queue' => ['default'], 'balance' => false,
            'maxProcesses' => 1, 'memory' => 256, 'tries' => 1, 'timeout' => 3660, 'nice' => 0,
        ],
        'short' => [
            'connection' => 'redis-short', 'queue' => ['mail', 'short'], 'balance' => false,
            'maxProcesses' => 1, 'memory' => 128, 'tries' => 3, 'timeout' => 90, 'nice' => 0,
        ],
        'imports' => [
            'connection' => 'redis-imports', 'queue' => ['imports'], 'balance' => false,
            'maxProcesses' => 1, 'memory' => 256, 'tries' => 1, 'timeout' => 3660, 'nice' => 5,
        ],
        'images' => [
            'connection' => 'redis-short', 'queue' => ['species-images'], 'balance' => false,
            'maxProcesses' => 1, 'memory' => 192, 'tries' => 3, 'timeout' => 150, 'nice' => 10,
        ],
        'bios' => [
            'connection' => 'redis-short', 'queue' => ['species-bios'], 'balance' => false,
            'maxProcesses' => 1, 'memory' => 128, 'tries' => 3, 'timeout' => 150, 'nice' => 10,
        ],
    ],
    'environments' => [
        'production' => ['legacy' => [], 'short' => [], 'imports' => [], 'images' => [], 'bios' => []],
        'local' => ['legacy' => [], 'short' => [], 'imports' => [], 'images' => [], 'bios' => []],
        '*' => ['legacy' => [], 'short' => [], 'imports' => [], 'images' => [], 'bios' => []],
    ],
];
