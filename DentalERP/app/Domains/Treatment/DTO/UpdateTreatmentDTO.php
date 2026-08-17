<?php

declare(strict_types=1);

namespace App\Domains\Treatment\DTO;

final readonly class UpdateTreatmentDTO
{
    public function __construct(
        public ?string $doctorId = null,
        public ?string $treatmentType = null,
        public ?string $status = null,
        public ?string $cost = null,
        public ?string $description = null,
        public ?array $procedureData = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'doctor_id'      => $this->doctorId,
            'treatment_type' => $this->treatmentType,
            'status'         => $this->status,
            'cost'           => $this->cost,
            'description'    => $this->description,
            'procedure_data' => $this->procedureData,
        ], fn ($v) => $v !== null);
    }
}