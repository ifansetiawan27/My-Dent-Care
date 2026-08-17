<?php

declare(strict_types=1);

namespace App\Domains\Asset\Repositories;

use App\Domains\Asset\Interfaces\AssetRepositoryInterface;
use App\Domains\Asset\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AssetRepository implements AssetRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Asset::where('organization_id', $filters['organization_id']);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('asset_code', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('description', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'created_at', 'purchase_date'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Asset
    {
        return Asset::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($data);
        return $asset->refresh();
    }

    public function delete(Asset $asset): bool
    {
        return (bool) $asset->delete();
    }
}