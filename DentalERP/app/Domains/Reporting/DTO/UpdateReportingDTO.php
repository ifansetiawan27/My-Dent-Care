<?php

declare(strict_types=1);

namespace App\Domains\Reporting\DTO;

final readonly class UpdateReportingDTO
{
    public function __construct(
        public ?string $reportType = null,
        public ?string $name = null,
        public ?string $status = null,
        public ?string $reportDate = null,
        public ?array $parameters = null,
        public ?array $data = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'report_type' => $this->reportType,
            'name'        => $this->name,
            'status'      => $this->status,
            'report_date' => $this->reportDate,
            'parameters'  => $this->parameters,
            'data'        => $this->data,
        ], fn ($v) => $v !== null);
    }
}