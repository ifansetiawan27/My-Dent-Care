<?php

declare(strict_types=1);

namespace App\Domains\Employee\Interfaces;

use App\Domains\Employee\DTO\CreateEmployeeDTO;
use App\Domains\Employee\DTO\UpdateEmployeeDTO;
use App\Domains\Employee\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function findById(string $id, string $organizationId): Employee;

    public function create(CreateEmployeeDTO $dto): Employee;

    public function update(string $id, UpdateEmployeeDTO $dto, string $organizationId): Employee;

    public function delete(string $id, string $organizationId): bool;

    public function toggleActive(string $id, string $organizationId): Employee;
}