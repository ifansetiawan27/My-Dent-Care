<?php

declare(strict_types=1);

namespace App\Domains\Integration\DTO;

final readonly class CreateIntegrationConfigDTO
{
    public function __construct(
        public string $integrationType,
        public string $name,
        public string $organizationId,
        public bool $isActive = false,
        public ?string $endpointUrl = null,
        public ?string $apiKey = null,
        public ?string $apiSecret = null,
        public ?array $config = null,
    ) {}

    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'integration_type' => $this->integrationType,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'endpoint_url' => $this->endpointUrl,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'config' => $this->config,
        ];
    }
}
