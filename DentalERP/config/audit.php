<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Retention Period
    |--------------------------------------------------------------------------
    |
    | The number of days audit records should be retained in the database.
    | Per Indonesian medical records regulation, default is 7 years (2555 days).
    |
    */
    'retention_days' => env('AUDIT_RETENTION_DAYS', 2555),

    /*
    |--------------------------------------------------------------------------
    | Secret Field Names
    |--------------------------------------------------------------------------
    |
    | Field names that should be excluded from audit old_value/new_value.
    | These are sensitive fields that should never be logged.
    |
    */
    'secret_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'api_key',
        'api_secret',
        'access_token',
        'refresh_token',
        'secret',
        'private_key',
        'credit_card',
        'cvv',
        'ssn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Fields
    |--------------------------------------------------------------------------
    |
    | Field names that are sensitive but may be logged (e.g., ip_address).
    | Access should be restricted to authorized administrators.
    |
    */
    'sensitive_fields' => [
        'ip_address',
        'email',
        'phone',
        'phone_number',
        'mobile',
        'national_id',
        'tax_number',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Audit entries are persisted asynchronously via Queue.
    | Specify the queue connection and queue name.
    |
    */
    'queue' => [
        'connection' => env('AUDIT_QUEUE_CONNECTION', 'redis'),
        'name' => env('AUDIT_QUEUE_NAME', 'audit'),
    ],
];
