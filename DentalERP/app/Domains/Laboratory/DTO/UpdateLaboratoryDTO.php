<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\DTO;

final readonly class UpdateLaboratoryDTO
{
    public function __construct(
        public ?string $doctorId = null,
        public ?string $categoryId = null,
        public ?string $status = null,
        public ?string $description = null,
        public ?array $results = null,
        public ?string $orderedAt = null,
        public ?string $completedAt = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'doctor_id'    => $this->doctorId,
            'category_id'  => $this->categoryId,
            'status'       => $this->status,
            'description'  => $this->description,
            'results'      => $this->results,
            'ordered_at'   => $this->orderedAt,
            'completed_at' => $this->completedAt,
            'notes'        => $this->notes,
        ], fn ($v) => $v !== null);
    }
}