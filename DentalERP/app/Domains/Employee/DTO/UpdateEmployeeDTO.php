<?php

declare(strict_types=1);

namespace App\Domains\Employee\DTO;

final readonly class UpdateEmployeeDTO
{
    public function __construct(
        public ?string $employeeCode,
        public ?string $fullName,
        public ?string $branchId,
        public ?string $employmentStatus,
        public ?string $hireDate,
        public ?string $resignationDate,
        public ?string $position,
        public ?string $gender,
        public ?string $religion,
        public ?string $maritalStatus,
        public ?string $nationalityId,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $districtId,
        public ?string $villageId,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'employee_code'    => $this->employeeCode,
            'full_name'        => $this->fullName,
            'branch_id'        => $this->branchId,
            'employment_status'=> $this->employmentStatus,
            'hire_date'        => $this->hireDate,
            'resignation_date' => $this->resignationDate,
            'position'         => $this->position,
            'gender'           => $this->gender,
            'religion'         => $this->religion,
            'marital_status'   => $this->maritalStatus,
            'nationality_id'   => $this->nationalityId,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'address'          => $this->address,
            'district_id'      => $this->districtId,
            'village_id'       => $this->villageId,
        ], fn ($v) => $v !== null);
    }
}