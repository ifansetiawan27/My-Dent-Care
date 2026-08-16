<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\DTO;

final readonly class UpdateOdontogramDTO
{
    public function __construct(
        public ?string $toothType = null,
        public ?string $surface = null,
        public ?string $condition = null,
        public ?string $notes = null,
        public ?array $findings = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'tooth_type' => $this->toothType,
            'surface'    => $this->surface,
            'condition'  => $this->condition,
            'notes'      => $this->notes,
            'findings'   => $this->findings,
        ], fn ($v) => $v !== null);
    }
}