<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Repositories;

use App\Domains\Doctor\Interfaces\DoctorRepositoryInterface;
use App\Domains\Doctor\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class DoctorRepository implements DoctorRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Doctor::where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['specialty_id'])) {
            $query->where('specialty_id', $filters['specialty_id']);
        }
        if (! empty($filters['search'])) {
            $query->where(fn ($q) => $q->where('full_name', 'ILIKE', "%{$filters['search']}%")
                ->orWhere('doctor_code', 'ILIKE', "%{$filters['search']}%"));
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['full_name', 'doctor_code', 'hire_date'])
            ? $filters['sort_by'] : 'full_name';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'asc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Doctor
    {
        return Doctor::where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Doctor
    {
        return Doctor::create($data);
    }

    public function update(Doctor $doctor, array $data): Doctor
    {
        $doctor->update($data);
        return $doctor->refresh();
    }

    public function delete(Doctor $doctor): bool
    {
        return (bool) $doctor->delete();
    }

    public function existsByDoctorCode(string $code, ?string $excludeId = null): bool
    {
        $query = Doctor::where('doctor_code', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}