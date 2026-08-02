<?php

declare(strict_types=1);

namespace App\Platform\Webhook\Contracts;

use App\Platform\Webhook\DTO\WebhookPayloadDTO;

/**
 * WebhookServiceInterface
 *
 * The single contract for outgoing and incoming webhooks across the ERP.
 * Outgoing deliveries are dispatched via Queue with signing and retry.
 * Incoming webhooks are verified against their signature before processing.
 *
 * Platform rule: Domains depend on this interface only — never on raw HTTP clients.
 */
interface WebhookServiceInterface
{
    /**
     * Queue an outgoing webhook for delivery to a subscriber endpoint.
     * The payload is HMAC-signed and retried on failure.
     *
     * @param  WebhookPayloadDTO $payload
     * @return void
     */
    public function send(WebhookPayloadDTO $payload): void;

    /**
     * Verify the signature of an incoming webhook request.
     *
     * @param  string $rawBody    Raw request body.
     * @param  string $signature  Signature header value.
     * @param  string $secret     Shared secret to verify against.
     * @return bool
     */
    public function verifySignature(string $rawBody, string $signature, string $secret): bool;

    /**
     * Generate an HMAC signature for a payload body.
     *
     * @param  string $rawBody
     * @param  string $secret
     * @return string
     */
    public function sign(string $rawBody, string $secret): string;
}
