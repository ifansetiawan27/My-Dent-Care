<?php

declare(strict_types=1);

namespace App\Platform\IntegrationHub\DTO;

use App\Platform\IntegrationHub\Enums\IntegrationProvider;

/**
 * IntegrationRequestDTO
 *
 * Immutable value object describing a request to an external provider
 * routed through the Integration Hub.
 */
final readonly class IntegrationRequestDTO
{
    /**
     * @param  IntegrationProvider   $provider        Target external system.
     * @param  string                $operation       Logical operation (e.g. 'create_encounter').
     * @param  array<string, mixed>  $payload         Request payload.
     * @param  string|null           $organizationId  Tenant organization UUID (for per-tenant credentials).
     * @param  array<string, string> $headers         Additional headers.
     * @param  string|null           $idempotencyKey  Idempotency key for safe retries.
     */
    public function __construct(
        public IntegrationProvider $provider,
        public string              $operation,
        public array               $payload         = [],
        public ?string             $organizationId  = null,
        public array               $headers         = [],
        public ?string             $idempotencyKey  = null,
    ) {}
}
