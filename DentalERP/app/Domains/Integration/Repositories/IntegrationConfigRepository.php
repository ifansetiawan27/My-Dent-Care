<?php

declare(strict_types=1);

namespace App\Domains\Integration\Repositories;

use App\Domains\Integration\Interfaces\IntegrationConfigRepositoryInterface;
use App\Domains\Integration\Models\IntegrationConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IntegrationConfigRepository implements IntegrationConfigRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = IntegrationConfig::where('organization_id', $filters['organization_id']);

        if (! empty($filters['integration_type'])) {
            $query->where('integration_type', $filters['integration_type']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('endpoint_url', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'last_sync_at', 'name'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?IntegrationConfig
    {
        return IntegrationConfig::where('id', $id)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function create(array $data): IntegrationConfig
    {
        return IntegrationConfig::create($data);
    }

    public function update(IntegrationConfig $config, array $data): IntegrationConfig
    {
        $config->update($data);
        return $config->refresh();
    }

    public function delete(IntegrationConfig $config): bool
    {
        return (bool) $config->delete();
    }
}
