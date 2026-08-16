<?php

declare(strict_types=1);

namespace App\Domains\Employee\Repositories;

use App\Domains\Employee\Interfaces\EmployeeRepositoryInterface;
use App\Domains\Employee\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Employee::query()->where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('full_name', 'ILIKE', "%{$filters['search']}%")
                  ->orWhere('employee_code', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['full_name', 'employee_code', 'hire_date'])
            ? $filters['sort_by']
            : 'full_name';

        return $query->orderBy($sortBy, $filters['sort_dir'] ?? 'asc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Employee
    {
        return Employee::where('id', $id)
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee->refresh();
    }

    public function delete(Employee $employee): bool
    {
        return (bool) $employee->delete();
    }

    public function existsByEmployeeCode(string $code, ?string $excludeId = null): bool
    {
        $query = Employee::where('employee_code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}