<?php

declare(strict_types=1);

namespace App\Domains\Billing\Repositories;

use App\Domains\Billing\Interfaces\BillingRepositoryInterface;
use App\Domains\Billing\Models\Billing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BillingRepository implements BillingRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Billing::where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('invoice_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('notes', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'due_date'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Billing
    {
        return Billing::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Billing
    {
        return Billing::create($data);
    }

    public function update(Billing $billing, array $data): Billing
    {
        $billing->update($data);
        return $billing->refresh();
    }

    public function delete(Billing $billing): bool
    {
        return (bool) $billing->delete();
    }
}