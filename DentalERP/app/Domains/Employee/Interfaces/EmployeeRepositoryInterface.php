<?php

declare(strict_types=1);

namespace App\Domains\Employee\Interfaces;

use App\Domains\Employee\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function findById(string $id, string $organizationId): ?Employee;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function delete(Employee $employee): bool;

    public function existsByEmployeeCode(string $code, ?string $excludeId = null): bool;
}