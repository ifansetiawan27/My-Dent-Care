<?php

declare(strict_types=1);

namespace App\Domains\Treatment\DTO;

final readonly class CreateTreatmentDTO
{
    public function __construct(
        public string $patientId,
        public string $treatmentType,
        public string $organizationId,
        public ?string $doctorId = null,
        public ?string $appointmentId = null,
        public ?string $cost = null,
        public ?string $description = null,
        public ?array $procedureData = null,
    ) {}

    public function toArray(): array
    {
        return [
            'patient_id'      => $this->patientId,
            'doctor_id'       => $this->doctorId,
            'appointment_id'  => $this->appointmentId,
            'treatment_type'  => $this->treatmentType,
            'cost'            => $this->cost,
            'description'     => $this->description,
            'procedure_data'  => $this->procedureData,
            'organization_id' => $this->organizationId,
            'status'          => 'planned',
        ];
    }
}