<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Repositories;

use App\Domains\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Models\Dashboard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DashboardRepository implements DashboardRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Dashboard::where('organization_id', $filters['organization_id']);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['name', 'created_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Dashboard
    {
        return Dashboard::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Dashboard
    {
        return Dashboard::create($data);
    }

    public function update(Dashboard $dashboard, array $data): Dashboard
    {
        $dashboard->update($data);
        return $dashboard->refresh();
    }

    public function delete(Dashboard $dashboard): bool
    {
        return (bool) $dashboard->delete();
    }
}