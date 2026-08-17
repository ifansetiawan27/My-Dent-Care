<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Interfaces;

use App\Domains\IntegrationHub\DTO\CreateIntegrationDTO;
use App\Domains\IntegrationHub\DTO\UpdateIntegrationDTO;
use App\Domains\IntegrationHub\Models\IntegrationHub;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationHubServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function findById(string $id, string $organizationId): IntegrationHub;

    public function create(CreateIntegrationDTO $dto): IntegrationHub;

    public function update(string $id, UpdateIntegrationDTO $dto, string $organizationId): IntegrationHub;

    public function delete(string $id, string $organizationId): bool;

    public function toggleActive(string $id, string $organizationId): IntegrationHub;
}