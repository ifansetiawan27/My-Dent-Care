<?php

declare(strict_types=1);

namespace App\Domains\Doctor\DTO;

final readonly class UpdateDoctorDTO
{
    public function __construct(
        public ?string $doctorCode = null,
        public ?string $fullName = null,
        public ?string $branchId = null,
        public ?string $specialtyId = null,
        public ?string $licenseNumber = null,
        public ?string $consultationFee = null,
        public ?string $gender = null,
        public ?string $religion = null,
        public ?string $maritalStatus = null,
        public ?string $nationalityId = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $districtId = null,
        public ?string $villageId = null,
        public ?string $hireDate = null,
        public ?string $resignationDate = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'doctor_code'      => $this->doctorCode,
            'full_name'        => $this->fullName,
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
        ], fn ($v) => $v !== null);
    }
}