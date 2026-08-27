<?php

declare(strict_types=1);

namespace App\Domains\Integration\Repositories;

use App\Domains\Integration\Interfaces\IntegrationLogRepositoryInterface;
use App\Domains\Integration\Models\IntegrationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class IntegrationLogRepository implements IntegrationLogRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = IntegrationLog::whereHas('config', function ($q) use ($filters): void {
            $q->where('organization_id', $filters['organization_id']);
        });

        if (! empty($filters['integration_config_id'])) {
            $query->where('integration_config_id', $filters['integration_config_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'duration_ms'])
            ? $filters['sort_by'] : 'created_at';

        return $query->with('config')
            ->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id): ?IntegrationLog
    {
        return IntegrationLog::with('config')->find($id);
    }

    public function create(array $data): IntegrationLog
    {
        return IntegrationLog::create($data);
    }
}
