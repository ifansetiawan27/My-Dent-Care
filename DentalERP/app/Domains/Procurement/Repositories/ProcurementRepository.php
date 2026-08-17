<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Repositories;

use App\Domains\Procurement\Interfaces\ProcurementRepositoryInterface;
use App\Domains\Procurement\Models\Procurement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProcurementRepository implements ProcurementRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Procurement::where('organization_id', $filters['organization_id']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('order_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('notes', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['order_date', 'created_at', 'status'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Procurement
    {
        return Procurement::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Procurement
    {
        return Procurement::create($data);
    }

    public function update(Procurement $procurement, array $data): Procurement
    {
        $procurement->update($data);
        return $procurement->refresh();
    }

    public function delete(Procurement $procurement): bool
    {
        return (bool) $procurement->delete();
    }
}