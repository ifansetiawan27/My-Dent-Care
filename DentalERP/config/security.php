<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Security Response Headers
    |--------------------------------------------------------------------------
    |
    | Baseline hardening headers applied by
    | App\Platform\Http\Middleware\SecurityHeaders to every API response.
    | Set a value to null to omit that header entirely.
    |
    | The API returns JSON only and never renders HTML, so the default CSP is
    | intentionally restrictive: nothing may be loaded, framed, or executed.
    |
    */

    'headers' => [
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_frame_options'        => env('SECURITY_X_FRAME_OPTIONS', 'DENY'),
        'referrer_policy'        => env('SECURITY_REFERRER_POLICY', 'no-referrer'),

        'permissions_policy' => env(
            'SECURITY_PERMISSIONS_POLICY',
            'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()',
        ),

        'content_security_policy' => env(
            'SECURITY_CONTENT_SECURITY_POLICY',
            "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'",
        ),

        'cross_origin_opener_policy'   => env('SECURITY_CROSS_ORIGIN_OPENER_POLICY', null),
        'cross_origin_resource_policy' => env('SECURITY_CROSS_ORIGIN_RESOURCE_POLICY', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security
    |--------------------------------------------------------------------------
    |
    | Only emitted on HTTPS requests. Keep this disabled in local and testing
    | environments, and enable it in staging and production where TLS is
    | terminated in front of the application.
    |
    */

    'hsts' => [
        'enabled'            => env('SECURITY_HSTS_ENABLED', false),
        'max_age'            => env('SECURITY_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload'            => env('SECURITY_HSTS_PRELOAD', false),
    ],

];
