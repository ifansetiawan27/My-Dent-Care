<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Repositories;

use App\Domains\Inventory\Interfaces\InventoryRepositoryInterface;
use App\Domains\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InventoryRepository implements InventoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Inventory::where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('item_code', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('description', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'created_at', 'quantity'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Inventory
    {
        return Inventory::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Inventory
    {
        return Inventory::create($data);
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        $inventory->update($data);
        return $inventory->refresh();
    }

    public function delete(Inventory $inventory): bool
    {
        return (bool) $inventory->delete();
    }
}