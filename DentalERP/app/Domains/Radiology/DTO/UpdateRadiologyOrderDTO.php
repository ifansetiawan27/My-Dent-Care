<?php

declare(strict_types=1);

namespace App\Domains\Radiology\DTO;

final readonly class UpdateRadiologyOrderDTO
{
    public function __construct(
        public ?string $patientId = null,
        public ?string $doctorId = null,
        public ?string $radiologyType = null,
        public ?string $bodyPart = null,
        public ?string $clinicalNotes = null,
        public ?string $priority = null,
        public ?string $status = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'patient_id'     => $this->patientId,
            'doctor_id'      => $this->doctorId,
            'radiology_type' => $this->radiologyType,
            'body_part'      => $this->bodyPart,
            'clinical_notes' => $this->clinicalNotes,
            'priority'       => $this->priority,
            'status'         => $this->status,
        ], fn ($v) => $v !== null);
    }
}
