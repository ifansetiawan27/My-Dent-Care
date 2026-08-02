<?php

declare(strict_types=1);

namespace App\Platform\IntegrationHub\Contracts;

use App\Platform\IntegrationHub\DTO\IntegrationRequestDTO;
use App\Platform\IntegrationHub\DTO\IntegrationResponseDTO;
use App\Platform\IntegrationHub\Enums\IntegrationProvider;

/**
 * IntegrationServiceInterface
 *
 * The single entry point for all external integrations across the ERP.
 * Routes each request to the correct connector, handles retries, logging,
 * and normalizes responses into IntegrationResponseDTO.
 *
 * Platform rule: Domains depend on this interface only — never on a specific
 * provider SDK or HTTP client.
 */
interface IntegrationServiceInterface
{
    /**
     * Send a request to an external provider synchronously and return the response.
     *
     * @param  IntegrationRequestDTO  $request
     * @return IntegrationResponseDTO
     */
    public function send(IntegrationRequestDTO $request): IntegrationResponseDTO;

    /**
     * Queue a request to an external provider for asynchronous processing.
     * Use for non-blocking, retry-safe operations.
     *
     * @param  IntegrationRequestDTO $request
     * @return void
     */
    public function sendAsync(IntegrationRequestDTO $request): void;

    /**
     * Determine whether a provider is available for the given organization.
     *
     * @param  IntegrationProvider $provider
     * @param  string              $organizationId
     * @return bool
     */
    public function isAvailable(IntegrationProvider $provider, string $organizationId): bool;
}
