<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\DTO;

final readonly class CreateLaboratoryDTO
{
    public function __construct(
        public string $patientId,
        public string $orderNumber,
        public string $organizationId,
        public string $orderedAt,
        public ?string $doctorId = null,
        public ?string $categoryId = null,
        public ?string $description = null,
        public ?array $results = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'patient_id'      => $this->patientId,
            'order_number'    => $this->orderNumber,
            'organization_id' => $this->organizationId,
            'doctor_id'       => $this->doctorId,
            'category_id'     => $this->categoryId,
            'description'     => $this->description,
            'results'         => $this->results,
            'ordered_at'      => $this->orderedAt,
            'notes'           => $this->notes,
            'status'          => 'pending',
        ];
    }
}