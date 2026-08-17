<?php

declare(strict_types=1);

namespace App\Domains\Reporting\DTO;

final readonly class CreateReportingDTO
{
    public function __construct(
        public string $reportType,
        public string $name,
        public string $reportDate,
        public string $organizationId,
        public ?array $parameters = null,
        public ?array $data = null,
    ) {}

    public function toArray(): array
    {
        return [
            'report_type'     => $this->reportType,
            'name'            => $this->name,
            'report_date'     => $this->reportDate,
            'organization_id' => $this->organizationId,
            'parameters'      => $this->parameters,
            'data'            => $this->data,
            'status'          => 'generated',
        ];
    }
}