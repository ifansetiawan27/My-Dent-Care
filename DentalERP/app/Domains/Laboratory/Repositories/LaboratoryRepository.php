<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Repositories;

use App\Domains\Laboratory\Interfaces\LaboratoryRepositoryInterface;
use App\Domains\Laboratory\Models\Laboratory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class LaboratoryRepository implements LaboratoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Laboratory::where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('description', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('order_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('notes', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'status', 'ordered_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Laboratory
    {
        return Laboratory::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Laboratory
    {
        return Laboratory::create($data);
    }

    public function update(Laboratory $laboratory, array $data): Laboratory
    {
        $laboratory->update($data);
        return $laboratory->refresh();
    }

    public function delete(Laboratory $laboratory): bool
    {
        return (bool) $laboratory->delete();
    }
}