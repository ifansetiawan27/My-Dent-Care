<?php

declare(strict_types=1);

namespace App\Domains\Finance\Interfaces;

use App\Core\Contracts\ServiceInterface;
use App\Domains\Finance\Models\FinancialReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FinancialReportServiceInterface extends ServiceInterface
{
    public function paginate(array $params = []): LengthAwarePaginator;
    public function findByIdWithOrganization(string $id, string $organizationId): FinancialReport;
    public function createForOrganization(array $data, string $organizationId): FinancialReport;
    public function updateForOrganization(string $id, array $data, string $organizationId): FinancialReport;
    public function deleteForOrganization(string $id, string $organizationId): bool;
    public function generateReport(string $id, string $organizationId): FinancialReport;
}
