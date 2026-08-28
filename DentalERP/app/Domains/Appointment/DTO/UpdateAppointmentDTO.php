<?php

declare(strict_types=1);

namespace App\Domains\Appointment\DTO;

final readonly class UpdateAppointmentDTO
{
    public function __construct(
        public ?string $scheduledAt = null,
        public ?string $endAt = null,
        public ?string $status = null,
        public ?string $type = null,
        public ?string $notes = null,
        public ?int $reminderMinutes = null,
        public ?int $durationMinutes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'scheduled_at' => $this->scheduledAt,
            'end_at'       => $this->endAt,
            'status'       => $this->status,
            'type'         => $this->type,
            'notes'        => $this->notes,
            'reminder_minutes' => $this->reminderMinutes,
            'duration_minutes' => $this->durationMinutes,
        ], fn ($v) => $v !== null);
    }
}