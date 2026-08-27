<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\RepositoryInterface;
use App\Domains\Finance\Models\FinancialReport;

interface FinancialReportRepositoryInterface extends RepositoryInterface
{
    public function findByType(string $reportType, string $organizationId): array;
    public function findByPeriod(string $organizationId, string $startDate, string $endDate): array;
}
