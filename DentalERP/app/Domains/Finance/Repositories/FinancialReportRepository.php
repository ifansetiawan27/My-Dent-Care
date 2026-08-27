<?php

declare(strict_types=1);

namespace App\Domains\Finance\Repositories;

use App\Core\Base\BaseRepository;
use App\Domains\Finance\Interfaces\FinancialReportRepositoryInterface;
use App\Domains\Finance\Models\FinancialReport;

class FinancialReportRepository extends BaseRepository implements FinancialReportRepositoryInterface
{
    public function __construct(FinancialReport $model)
    {
        parent::__construct($model);
    }

    public function findByType(string $reportType, string $organizationId): array
    {
        return $this->model->where('report_type', $reportType)
            ->where('organization_id', $organizationId)
            ->orderByDesc('created_at')
            ->get()->all();
    }

    public function findByPeriod(string $organizationId, string $startDate, string $endDate): array
    {
        return $this->model->where('organization_id', $organizationId)
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate)
            ->orderByDesc('created_at')
            ->get()->all();
    }
}
