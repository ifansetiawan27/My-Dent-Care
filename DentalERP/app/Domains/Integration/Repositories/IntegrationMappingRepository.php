<?php

declare(strict_types=1);

namespace App\Domains\Integration\Repositories;

use App\Domains\Integration\Interfaces\IntegrationMappingRepositoryInterface;
use App\Domains\Integration\Models\IntegrationMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IntegrationMappingRepository implements IntegrationMappingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = IntegrationMapping::whereHas('config', function ($q) use ($filters): void {
            $q->where('organization_id', $filters['organization_id']);
        });

        if (! empty($filters['integration_config_id'])) {
            $query->where('integration_config_id', $filters['integration_config_id']);
        }
        if (! empty($filters['local_type'])) {
            $query->where('local_type', $filters['local_type']);
        }
        if (isset($filters['is_synced'])) {
            $query->where('is_synced', $filters['is_synced']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('local_type', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('external_code', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'external_code', 'local_type'])
            ? $filters['sort_by'] : 'created_at';

        return $query->with('config')
            ->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?IntegrationMapping
    {
        return IntegrationMapping::whereHas('config', function ($q) use ($organizationId): void {
                $q->where('organization_id', $organizationId);
            })
            ->where('id', $id)
            ->with('config')
            ->first();
    }

    public function findByExternalCode(string $integrationConfigId, string $localType, string $externalCode): ?IntegrationMapping
    {
        return IntegrationMapping::where('integration_config_id', $integrationConfigId)
            ->where('local_type', $localType)
            ->where('external_code', $externalCode)
            ->first();
    }

    public function create(array $data): IntegrationMapping
    {
        return IntegrationMapping::create($data);
    }

    public function update(IntegrationMapping $mapping, array $data): IntegrationMapping
    {
        $mapping->update($data);
        return $mapping->refresh();
    }

    public function delete(IntegrationMapping $mapping): bool
    {
        return (bool) $mapping->delete();
    }
}
