<?php

declare(strict_types=1);

namespace App\Domains\EMR\DTO;

final readonly class UpdateEMRDTO
{
    public function __construct(
        public ?string $chiefComplaint = null,
        public ?string $diagnosis = null,
        public ?string $treatmentNotes = null,
        public ?array $vitalSigns = null,
        public ?string $status = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'chief_complaint' => $this->chiefComplaint,
            'diagnosis'       => $this->diagnosis,
            'treatment_notes' => $this->treatmentNotes,
            'vital_signs'     => $this->vitalSigns,
            'status'          => $this->status,
        ], fn ($v) => $v !== null);
    }
}