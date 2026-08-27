<?php

declare(strict_types=1);

namespace App\Domains\Integration\DTO;

final readonly class CreateIntegrationMappingDTO
{
    public function __construct(
        public string $integrationConfigId,
        public string $localType,
        public string $localId,
        public string $externalCode,
        public bool $isSynced = false,
        public ?array $externalData = null,
    ) {}

    public function toArray(): array
    {
        return [
            'integration_config_id' => $this->integrationConfigId,
            'local_type' => $this->localType,
            'local_id' => $this->localId,
            'external_code' => $this->externalCode,
            'external_data' => $this->externalData,
            'is_synced' => $this->isSynced,
        ];
    }
}
