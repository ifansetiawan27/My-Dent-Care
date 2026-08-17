<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Interfaces;

use App\Domains\IntegrationHub\Models\IntegrationHub;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationHubRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function findById(string $id, string $organizationId): ?IntegrationHub;

    public function findByProvider(string $provider, string $organizationId, ?string $excludeId = null): ?IntegrationHub;

    public function create(array $data): IntegrationHub;

    public function update(IntegrationHub $integration, array $data): IntegrationHub;

    public function delete(IntegrationHub $integration): bool;
}