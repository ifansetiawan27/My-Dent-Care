<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Available notification channels and their configurations.
    |
    */
    'channels' => [
        'email' => [
            'enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
            'driver' => env('MAIL_MAILER', 'smtp'),
        ],

        'whatsapp' => [
            'enabled' => env('NOTIFICATION_WHATSAPP_ENABLED', false),
            'api_url' => env('WHATSAPP_API_URL'),
            'api_key' => env('WHATSAPP_API_KEY'),
        ],

        'sms' => [
            'enabled' => env('NOTIFICATION_SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'api_key' => env('SMS_API_KEY'),
        ],

        'push' => [
            'enabled' => env('NOTIFICATION_PUSH_ENABLED', false),
            'fcm_server_key' => env('FCM_SERVER_KEY'),
        ],

        'in_app' => [
            'enabled' => true,
            'realtime' => env('NOTIFICATION_REALTIME_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Notifications are dispatched asynchronously via Queue.
    |
    */
    'queue' => [
        'connection' => env('NOTIFICATION_QUEUE_CONNECTION', 'redis'),
        'name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Maximum retry attempts for failed notifications.
    |
    */
    'max_retries' => env('NOTIFICATION_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    |
    | Default notification templates path.
    |
    */
    'templates_path' => resource_path('views/notifications'),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | Default language for notifications.
    |
    */
    'default_locale' => 'id',
];
