<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Bridge URL
    |--------------------------------------------------------------------------
    |
    | URL of the Node.js WhatsApp bridge service (baileys-based).
    | This service handles QR code generation and message sending.
    |
    */
    'bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://localhost:3000'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for HTTP requests to the WhatsApp bridge.
    |
    */
    'timeout' => (int) env('WHATSAPP_BRIDGE_TIMEOUT', 30),
];
