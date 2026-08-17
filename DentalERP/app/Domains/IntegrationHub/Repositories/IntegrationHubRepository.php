<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Repositories;

use App\Domains\IntegrationHub\Interfaces\IntegrationHubRepositoryInterface;
use App\Domains\IntegrationHub\Models\IntegrationHub;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IntegrationHubRepository implements IntegrationHubRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = IntegrationHub::where('organization_id', $filters['organization_id']);

        if (! empty($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'provider', 'created_at'], true)
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 15), 100));
    }

    public function findById(string $id, string $organizationId): ?IntegrationHub
    {
        return IntegrationHub::where('id', $id)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function findByProvider(string $provider, string $organizationId, ?string $excludeId = null): ?IntegrationHub
    {
        $query = IntegrationHub::where('provider', $provider)
            ->where('organization_id', $organizationId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    public function create(array $data): IntegrationHub
    {
        return IntegrationHub::create($data);
    }

    public function update(IntegrationHub $integration, array $data): IntegrationHub
    {
        $integration->update($data);

        return $integration->refresh();
    }

    public function delete(IntegrationHub $integration): bool
    {
        return (bool) $integration->delete();
    }
}