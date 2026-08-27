<?php

declare(strict_types=1);

namespace App\Domains\Integration\Interfaces;

use App\Domains\Integration\Models\IntegrationMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationMappingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?IntegrationMapping;
    public function findByExternalCode(string $integrationConfigId, string $localType, string $externalCode): ?IntegrationMapping;
    public function create(array $data): IntegrationMapping;
    public function update(IntegrationMapping $mapping, array $data): IntegrationMapping;
    public function delete(IntegrationMapping $mapping): bool;
}
