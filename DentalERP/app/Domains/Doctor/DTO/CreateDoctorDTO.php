<?php

declare(strict_types=1);

namespace App\Domains\Doctor\DTO;

final readonly class CreateDoctorDTO
{
    public function __construct(
        public string $doctorCode,
        public string $fullName,
        public string $organizationId,
        public ?string $branchId,
        public ?string $specialtyId,
        public ?string $licenseNumber,
        public ?string $consultationFee,
        public ?string $gender,
        public ?string $religion,
        public ?string $maritalStatus,
        public ?string $nationalityId,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $districtId,
        public ?string $villageId,
        public ?string $hireDate,
        public ?string $resignationDate,
    ) {}

    public function toArray(): array
    {
        return [
            'doctor_code'      => $this->doctorCode,
            'full_name'        => $this->fullName,
            'organization_id'  => $this->organizationId,
            'branch_id'        => $this->branchId,
            'specialty_id'     => $this->specialtyId,
            'license_number'   => $this->licenseNumber,
            'consultation_fee' => $this->consultationFee,
            'gender'           => $this->gender,
            'religion'         => $this->religion,
            'marital_status'   => $this->maritalStatus,
            'nationality_id'   => $this->nationalityId,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'address'          => $this->address,
            'district_id'      => $this->districtId,
            'village_id'       => $this->villageId,
            'hire_date'        => $this->hireDate,
            'resignation_date' => $this->resignationDate,
            'is_active'        => true,
        ];
    }
}