<?php

declare(strict_types=1);

namespace App\Domains\Patient\DTO;

final readonly class CreatePatientDTO
{
    public function __construct(
        public string $patientCode,
        public string $fullName,
        public string $organizationId,
        public ?string $branchId,
        public ?string $birthDate,
        public ?string $gender,
        public ?string $bloodType,
        public ?string $religion,
        public ?string $maritalStatus,
        public ?string $nationalityId,
        public ?string $patientTypeId,
        public ?string $phone,
        public ?string $email,
        public ?string $address,
        public ?string $districtId,
        public ?string $villageId,
    ) {}

    public function toArray(): array
    {
        return [
            'patient_code'    => $this->patientCode,
            'full_name'       => $this->fullName,
            'organization_id' => $this->organizationId,
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
            'is_active'       => true,
        ];
    }
}