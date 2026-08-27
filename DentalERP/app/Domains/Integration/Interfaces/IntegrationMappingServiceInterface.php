<?php

declare(strict_types=1);

namespace App\Domains\Integration\Interfaces;

use App\Domains\Integration\DTO\CreateIntegrationMappingDTO;
use App\Domains\Integration\DTO\UpdateIntegrationMappingDTO;
use App\Domains\Integration\Models\IntegrationMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationMappingServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): IntegrationMapping;
    public function create(CreateIntegrationMappingDTO $dto): IntegrationMapping;
    public function update(string $id, UpdateIntegrationMappingDTO $dto, string $organizationId): IntegrationMapping;
    public function delete(string $id, string $organizationId): bool;
    public function findByExternalCode(string $integrationConfigId, string $localType, string $externalCode): ?IntegrationMapping;
}
