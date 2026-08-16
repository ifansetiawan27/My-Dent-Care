<?php

declare(strict_types=1);

namespace App\Domains\Patient\DTO;

final readonly class UpdatePatientDTO
{
    public function __construct(
        public ?string $patientCode = null,
        public ?string $fullName = null,
        public ?string $branchId = null,
        public ?string $birthDate = null,
        public ?string $gender = null,
        public ?string $bloodType = null,
        public ?string $religion = null,
        public ?string $maritalStatus = null,
        public ?string $nationalityId = null,
        public ?string $patientTypeId = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $districtId = null,
        public ?string $villageId = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'patient_code'    => $this->patientCode,
            'full_name'       => $this->fullName,
            'branch_id'       => $this->branchId,
            'birth_date'      => $this->birthDate,
            'gender'          => $this->gender,
            'blood_type'      => $this->bloodType,
            'religion'        => $this->religion,
            'marital_status'  => $this->maritalStatus,
            'nationality_id'  => $this->nationalityId,
            'patient_type_id' => $this->patientTypeId,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'address'         => $this->address,
            'district_id'     => $this->districtId,
            'village_id'      => $this->villageId,
        ], fn ($v) => $v !== null);
    }
}