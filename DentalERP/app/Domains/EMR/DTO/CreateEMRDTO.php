<?php

declare(strict_types=1);

namespace App\Domains\EMR\DTO;

final readonly class CreateEMRDTO
{
    public function __construct(
        public string $organizationId,
        public string $patientId,
        public ?string $doctorId,
        public ?string $appointmentId,
        public ?string $chiefComplaint,
        public ?string $diagnosis,
        public ?string $treatmentNotes,
        public ?array $vitalSigns,
        public ?string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'patient_id'      => $this->patientId,
            'doctor_id'       => $this->doctorId,
            'appointment_id'  => $this->appointmentId,
            'chief_complaint' => $this->chiefComplaint,
            'diagnosis'       => $this->diagnosis,
            'treatment_notes' => $this->treatmentNotes,
            'vital_signs'     => $this->vitalSigns,
            'status'          => $this->status ?? 'open',
        ];
    }
}