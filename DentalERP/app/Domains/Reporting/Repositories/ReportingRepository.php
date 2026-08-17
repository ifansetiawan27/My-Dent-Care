<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Repositories;

use App\Domains\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domains\Reporting\Models\Reporting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ReportingRepository implements ReportingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Reporting::where('organization_id', $filters['organization_id']);

        if (! empty($filters['report_type'])) {
            $query->where('report_type', $filters['report_type']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['report_date', 'created_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Reporting
    {
        return Reporting::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Reporting
    {
        return Reporting::create($data);
    }

    public function update(Reporting $reporting, array $data): Reporting
    {
        $reporting->update($data);
        return $reporting->refresh();
    }

    public function delete(Reporting $reporting): bool
    {
        return (bool) $reporting->delete();
    }
}