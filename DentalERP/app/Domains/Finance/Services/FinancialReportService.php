<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Interfaces\FinancialReportRepositoryInterface;
use App\Domains\Finance\Interfaces\FinancialReportServiceInterface;
use App\Domains\Finance\Models\FinancialReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class FinancialReportService implements FinancialReportServiceInterface
{
    public function __construct(
        private readonly FinancialReportRepositoryInterface $repository,
    ) {}

    public function paginate(array $params = []): LengthAwarePaginator
    {
        return $this->repository->paginate($params);
    }

    public function findByIdWithOrganization(string $id, string $organizationId): FinancialReport
    {
        $report = $this->repository->findById($id, $organizationId);
        if (! $report) {
            throw new NotFoundException('Financial Report not found.');
        }
        return $report;
    }

    public function createForOrganization(array $data, string $organizationId): FinancialReport
    {
        return DB::transaction(fn (): FinancialReport => $this->repository->create($data));
    }

    public function updateForOrganization(string $id, array $data, string $organizationId): FinancialReport
    {
        $report = $this->findByIdWithOrganization($id, $organizationId);
        return DB::transaction(fn (): FinancialReport => $this->repository->update($report, $data));
    }

    public function deleteForOrganization(string $id, string $organizationId): bool
    {
        $report = $this->findByIdWithOrganization($id, $organizationId);
        return $this->repository->delete($report);
    }

    public function getById(string $id): FinancialReport
    {
        $report = $this->repository->find($id);
        if (! $report) {
            throw new NotFoundException('Financial Report not found.');
        }
        return $report;
    }

    public function create(array $data): FinancialReport
    {
        return DB::transaction(fn (): FinancialReport => $this->repository->create($data));
    }

    public function update(string $id, array $data): FinancialReport
    {
        $report = $this->getById($id);
        return DB::transaction(fn (): FinancialReport => $this->repository->update($report, $data));
    }

    public function delete(string $id): bool
    {
        $report = $this->getById($id);
        return $this->repository->delete($report);
    }

    public function generateReport(string $id, string $organizationId): FinancialReport
    {
        $report = $this->findById($id, $organizationId);

        if ($report->status === 'generated') {
            throw new BusinessException('Report has already been generated.');
        }

        $data = [
            'status' => 'generated',
            'generated_by' => auth()->user()->id,
            'generated_at' => now(),
        ];

        return DB::transaction(fn (): FinancialReport => $this->repository->update($report, $data));
    }
}
