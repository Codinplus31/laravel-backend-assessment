<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'vendors'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'vendors',
        ],
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'vendors',
        ],
    ],

    'providers' => [
        'vendors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Vendor::class,
        ],
    ],

    'passwords' => [
        'vendors' => [
            'provider' => 'vendors',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
