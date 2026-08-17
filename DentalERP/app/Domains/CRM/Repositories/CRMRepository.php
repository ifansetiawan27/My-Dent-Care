<?php

declare(strict_types=1);

namespace App\Domains\CRM\Repositories;

use App\Domains\CRM\Interfaces\CRMRepositoryInterface;
use App\Domains\CRM\Models\CRM;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class CRMRepository implements CRMRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = CRM::where('organization_id', $filters['organization_id']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['contact_type'])) {
            $query->where('contact_type', $filters['contact_type']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('subject', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('message', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['follow_up_date', 'created_at', 'status'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?CRM
    {
        return CRM::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): CRM
    {
        return CRM::create($data);
    }

    public function update(CRM $crm, array $data): CRM
    {
        $crm->update($data);
        return $crm->refresh();
    }

    public function delete(CRM $crm): bool
    {
        return (bool) $crm->delete();
    }
}