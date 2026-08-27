<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTO;

readonly class CreateFinancialReportDTO
{
    public function __construct(
        public string $organizationId,
        public string $reportType,
        public string $reportName,
        public string $periodStart,
        public string $periodEnd,
        public ?array $filters = null,
        public ?string $exportFormat = null,
    ) {}
}
