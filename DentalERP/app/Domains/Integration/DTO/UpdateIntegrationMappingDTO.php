<?php

declare(strict_types=1);

namespace App\Domains\Integration\DTO;

final readonly class UpdateIntegrationMappingDTO
{
    public function __construct(
        public ?string $localType = null,
        public ?string $localId = null,
        public ?string $externalCode = null,
        public ?bool $isSynced = null,
        public ?array $externalData = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'local_type' => $this->localType,
            'local_id' => $this->localId,
            'external_code' => $this->externalCode,
            'external_data' => $this->externalData,
            'is_synced' => $this->isSynced,
        ], fn ($v) => $v !== null);
    }
}
