<?php

declare(strict_types=1);

namespace App\Domains\AI\Repositories;

use App\Domains\AI\Interfaces\AIRepositoryInterface;
use App\Domains\AI\Models\AI;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AIRepository implements AIRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = AI::where('organization_id', $filters['organization_id']);

        if (! empty($filters['query_type'])) {
            $query->where('query_type', $filters['query_type']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?AI
    {
        return AI::where('id', $id)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function create(array $data): AI
    {
        return AI::create($data);
    }

    public function update(AI $ai, array $data): AI
    {
        $ai->update($data);
        return $ai->refresh();
    }
}