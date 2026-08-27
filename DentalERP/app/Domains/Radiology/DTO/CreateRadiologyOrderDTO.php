<?php

declare(strict_types=1);

namespace App\Domains\Radiology\DTO;

final readonly class CreateRadiologyOrderDTO
{
    public function __construct(
        public string $patientId,
        public string $doctorId,
        public string $radiologyType,
        public string $priority,
        public string $organizationId,
        public ?string $bodyPart = null,
        public ?string $clinicalNotes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'patient_id'      => $this->patientId,
            'doctor_id'       => $this->doctorId,
            'radiology_type'  => $this->radiologyType,
            'body_part'       => $this->bodyPart,
            'clinical_notes'  => $this->clinicalNotes,
            'priority'        => $this->priority,
            'organization_id' => $this->organizationId,
            'status'          => 'ordered',
        ];
    }
}
