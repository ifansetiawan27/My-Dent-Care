<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\DTO;

final readonly class CreateOdontogramDTO
{
    public function __construct(
        public string $organizationId,
        public string $patientId,
        public string $toothNumber,
        public ?string $toothType,
        public ?string $surface,
        public ?string $condition,
        public ?string $notes,
        public ?array $findings,
    ) {}

    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'patient_id'      => $this->patientId,
            'tooth_number'    => $this->toothNumber,
            'tooth_type'      => $this->toothType,
            'surface'         => $this->surface,
            'condition'       => $this->condition,
            'notes'           => $this->notes,
            'findings'        => $this->findings,
        ];
    }
}