<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Repositories;

use App\Domains\Treatment\Interfaces\TreatmentRepositoryInterface;
use App\Domains\Treatment\Models\Treatment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TreatmentRepository implements TreatmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Treatment::where('organization_id', $filters['organization_id']);

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (! empty($filters['appointment_id'])) {
            $query->where('appointment_id', $filters['appointment_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['treatment_type'])) {
            $query->where('treatment_type', $filters['treatment_type']);
        }
        if (! empty($filters['search'])) {
            $query->where('description', 'ILIKE', "%{$filters['search']}%");
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['created_at', 'status'])
            ? $filters['sort_by'] : 'created_at';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Treatment
    {
        return Treatment::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Treatment
    {
        return Treatment::create($data);
    }

    public function update(Treatment $treatment, array $data): Treatment
    {
        $treatment->update($data);
        return $treatment->refresh();
    }

    public function delete(Treatment $treatment): bool
    {
        return (bool) $treatment->delete();
    }
}