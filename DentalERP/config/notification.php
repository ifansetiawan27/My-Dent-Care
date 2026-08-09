<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Queue
    |--------------------------------------------------------------------------
    */

    'queue' => env('NOTIFICATION_QUEUE', 'default'),

    'queue_connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retry_attempts' => env('NOTIFICATION_RETRY_ATTEMPTS', 3),

    'retry_backoff' => [60, 300, 900], // 1 min, 5 min, 15 min

    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [
        'email' => [
            'driver' => 'smtp',
        ],
        'whatsapp' => [
            'driver' => 'whatsapp',
        ],
        'sms' => [
            'driver' => 'sms',
        ],
        'push' => [
            'driver' => 'fcm',
        ],
        'in_app' => [
            'driver' => 'database',
        ],
    ],

];
