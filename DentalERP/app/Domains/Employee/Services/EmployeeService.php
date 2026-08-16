<?php

declare(strict_types=1);

namespace App\Domains\Employee\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\Models\Branch;
use App\Domains\Employee\DTO\CreateEmployeeDTO;
use App\Domains\Employee\DTO\UpdateEmployeeDTO;
use App\Domains\Employee\Interfaces\EmployeeRepositoryInterface;
use App\Domains\Employee\Interfaces\EmployeeServiceInterface;
use App\Domains\Employee\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EmployeeService implements EmployeeServiceInterface
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Employee
    {
        $employee = $this->repository->findById($id, $organizationId);

        if (! $employee) {
            throw new NotFoundException('Employee not found.');
        }

        return $employee;
    }

    public function create(CreateEmployeeDTO $dto): Employee
    {
        if ($this->repository->existsByEmployeeCode($dto->employeeCode)) {
            throw new BusinessException('Employee code already taken.');
        }

        return DB::transaction(fn (): Employee => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateEmployeeDTO $dto, string $organizationId): Employee
    {
        $employee = $this->findById($id, $organizationId);

        $data = $dto->toArray();

        if (isset($data['employee_code']) && $this->repository->existsByEmployeeCode($data['employee_code'], $id)) {
            throw new BusinessException('Employee code already taken.');
        }

        if (isset($data['branch_id']) && $data['branch_id']) {
            $branch = Branch::find($data['branch_id']);

            if (! $branch || $branch->organization_id !== $employee->organization_id) {
                throw new BusinessException('Branch must belong to the same organization.');
            }
        }

        return DB::transaction(fn (): Employee => $this->repository->update($employee, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Employee
    {
        $employee = $this->findById($id, $organizationId);

        return $this->repository->update($employee, ['is_active' => ! $employee->is_active]);
    }
}