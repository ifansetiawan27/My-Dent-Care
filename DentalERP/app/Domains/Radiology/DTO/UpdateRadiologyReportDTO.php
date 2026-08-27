<?php

declare(strict_types=1);

namespace App\Domains\Radiology\DTO;

final readonly class UpdateRadiologyReportDTO
{
    public function __construct(
        public ?string $findings = null,
        public ?string $impression = null,
        public ?string $diagnosis = null,
        public ?bool $isFinal = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'findings'   => $this->findings,
            'impression' => $this->impression,
            'diagnosis'  => $this->diagnosis,
            'is_final'   => $this->isFinal,
        ], fn ($v) => $v !== null);
    }
}
