<?php

declare(strict_types=1);

namespace App\Domains\Patient\Repositories;

use App\Domains\Patient\Interfaces\PatientRepositoryInterface;
use App\Domains\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PatientRepository implements PatientRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Patient::where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['patient_type_id'])) {
            $query->where('patient_type_id', $filters['patient_type_id']);
        }
        if (! empty($filters['search'])) {
            $query->where(fn ($q) => $q->where('full_name', 'ILIKE', "%{$filters['search']}%")
                ->orWhere('patient_code', 'ILIKE', "%{$filters['search']}%"));
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['full_name', 'patient_code', 'birth_date'])
            ? $filters['sort_by'] : 'full_name';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'asc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Patient
    {
        return Patient::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    public function update(Patient $patient, array $data): Patient
    {
        $patient->update($data);
        return $patient->refresh();
    }

    public function delete(Patient $patient): bool
    {
        return (bool) $patient->delete();
    }

    public function existsByPatientCode(string $code, ?string $excludeId = null): bool
    {
        $query = Patient::where('patient_code', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}