<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Repositories;

use App\Domains\Radiology\Interfaces\RadiologyOrderRepositoryInterface;
use App\Domains\Radiology\Models\RadiologyOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RadiologyOrderRepository implements RadiologyOrderRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = RadiologyOrder::where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['radiology_type'])) {
            $query->where('radiology_type', $filters['radiology_type']);
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('order_number', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('clinical_notes', 'ILIKE', "%{$filters['search']}%");
            });
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'ordered_at', 'completed_at'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?RadiologyOrder
    {
        return RadiologyOrder::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): RadiologyOrder
    {
        return RadiologyOrder::create($data);
    }

    public function update(RadiologyOrder $order, array $data): RadiologyOrder
    {
        $order->update($data);
        return $order->refresh();
    }

    public function delete(RadiologyOrder $order): bool
    {
        return (bool) $order->delete();
    }
}
