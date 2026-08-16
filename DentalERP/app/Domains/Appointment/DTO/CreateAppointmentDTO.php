<?php

declare(strict_types=1);

namespace App\Domains\Appointment\DTO;

final readonly class CreateAppointmentDTO
{
    public function __construct(
        public string $organizationId,
        public ?string $branchId,
        public ?string $patientId,
        public ?string $doctorId,
        public string $scheduledAt,
        public ?string $endAt,
        public ?string $status,
        public ?string $type,
        public ?string $notes,
    ) {}

    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'patient_id'      => $this->patientId,
            'doctor_id'       => $this->doctorId,
            'scheduled_at'    => $this->scheduledAt,
            'end_at'          => $this->endAt,
            'status'          => $this->status ?? 'scheduled',
            'type'            => $this->type,
            'notes'           => $this->notes,
        ];
    }
}