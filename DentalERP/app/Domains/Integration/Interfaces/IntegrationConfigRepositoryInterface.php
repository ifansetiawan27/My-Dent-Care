<?php

declare(strict_types=1);

namespace App\Domains\Integration\Interfaces;

use App\Domains\Integration\Models\IntegrationConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationConfigRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?IntegrationConfig;
    public function create(array $data): IntegrationConfig;
    public function update(IntegrationConfig $config, array $data): IntegrationConfig;
    public function delete(IntegrationConfig $config): bool;
}
