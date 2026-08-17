<?php

declare(strict_types=1);

namespace App\Domains\HR\Repositories;

use App\Domains\HR\Interfaces\HRRepositoryInterface;
use App\Domains\HR\Models\HR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class HRRepository implements HRRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = HR::where('organization_id', $filters['organization_id']);

        if (! empty($filters['record_type'])) {
            $query->where('record_type', $filters['record_type']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('notes', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('record_type', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['effective_date', 'created_at', 'status'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?HR
    {
        return HR::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): HR
    {
        return HR::create($data);
    }

    public function update(HR $hr, array $data): HR
    {
        $hr->update($data);
        return $hr->refresh();
    }

    public function delete(HR $hr): bool
    {
        return (bool) $hr->delete();
    }
}