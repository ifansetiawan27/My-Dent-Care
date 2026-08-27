<?php

declare(strict_types=1);

namespace App\Domains\Integration\Interfaces;

use App\Domains\Integration\DTO\CreateIntegrationConfigDTO;
use App\Domains\Integration\DTO\UpdateIntegrationConfigDTO;
use App\Domains\Integration\Models\IntegrationConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationConfigServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): IntegrationConfig;
    public function create(CreateIntegrationConfigDTO $dto): IntegrationConfig;
    public function update(string $id, UpdateIntegrationConfigDTO $dto, string $organizationId): IntegrationConfig;
    public function delete(string $id, string $organizationId): bool;
    public function testConnection(string $id, string $organizationId): array;
}
