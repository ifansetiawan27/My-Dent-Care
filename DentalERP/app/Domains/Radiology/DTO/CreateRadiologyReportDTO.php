<?php

declare(strict_types=1);

namespace App\Domains\Radiology\DTO;

final readonly class CreateRadiologyReportDTO
{
    public function __construct(
        public string $radiologyOrderId,
        public string $radiologistId,
        public string $organizationId,
        public ?string $findings = null,
        public ?string $impression = null,
        public ?string $diagnosis = null,
        public ?bool $isFinal = null,
    ) {}

    public function toArray(): array
    {
        return [
            'radiology_order_id' => $this->radiologyOrderId,
            'radiologist_id'     => $this->radiologistId,
            'findings'           => $this->findings,
            'impression'         => $this->impression,
            'diagnosis'          => $this->diagnosis,
            'is_final'           => $this->isFinal ?? false,
            'organization_id'    => $this->organizationId,
        ];
    }
}
