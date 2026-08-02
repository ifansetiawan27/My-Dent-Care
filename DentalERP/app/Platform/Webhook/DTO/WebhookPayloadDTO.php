<?php

declare(strict_types=1);

namespace App\Platform\Webhook\DTO;

/**
 * WebhookPayloadDTO
 *
 * Immutable value object describing a webhook event to be delivered
 * to an external subscriber (outgoing) or received from a provider (incoming).
 */
final readonly class WebhookPayloadDTO
{
    /**
     * @param  string               $event           Event name (e.g. 'appointment.created').
     * @param  array<string, mixed> $payload         Event payload data.
     * @param  string               $url             Destination endpoint (outgoing).
     * @param  string|null          $organizationId  Tenant organization UUID.
     * @param  array<string, string> $headers        Extra HTTP headers.
     * @param  string|null          $secret          Shared secret for HMAC signing.
     * @param  string|null          $eventId         Idempotency identifier.
     */
    public function __construct(
        public string  $event,
        public array   $payload,
        public string  $url,
        public ?string $organizationId = null,
        public array   $headers        = [],
        public ?string $secret         = null,
        public ?string $eventId        = null,
    ) {}

    /**
     * Serialize the body that will be transmitted.
     *
     * @return array<string, mixed>
     */
    public function body(): array
    {
        return [
            'event'    => $this->event,
            'event_id' => $this->eventId,
            'data'     => $this->payload,
        ];
    }
}
