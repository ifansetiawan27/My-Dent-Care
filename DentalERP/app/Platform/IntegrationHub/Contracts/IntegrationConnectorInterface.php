<?php

declare(strict_types=1);

namespace App\Platform\IntegrationHub\Contracts;

use App\Platform\IntegrationHub\DTO\IntegrationRequestDTO;
use App\Platform\IntegrationHub\DTO\IntegrationResponseDTO;
use App\Platform\IntegrationHub\Enums\IntegrationProvider;

/**
 * IntegrationConnectorInterface
 *
 * Contract for a single external provider connector (SATUSEHAT, BPJS, PACS, etc.).
 * The Integration Hub resolves the correct connector per provider and delegates.
 *
 * Open/Closed: adding a new provider means adding a new connector implementing
 * this interface — no existing code is modified.
 */
interface IntegrationConnectorInterface
{
    /**
     * The provider this connector handles.
     */
    public function provider(): IntegrationProvider;

    /**
     * Execute a request against the external provider.
     *
     * @param  IntegrationRequestDTO  $request
     * @return IntegrationResponseDTO
     */
    public function execute(IntegrationRequestDTO $request): IntegrationResponseDTO;

    /**
     * Whether this connector is configured and enabled for the given organization.
     *
     * @param  string $organizationId
     * @return bool
     */
    public function isConfiguredFor(string $organizationId): bool;
}
