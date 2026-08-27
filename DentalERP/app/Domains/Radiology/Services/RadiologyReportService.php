<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyReportDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyReportDTO;
use App\Domains\Radiology\Interfaces\RadiologyReportRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyReportServiceInterface;
use App\Domains\Radiology\Models\RadiologyReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class RadiologyReportService implements RadiologyReportServiceInterface
{
    public function __construct(
        private readonly RadiologyReportRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): RadiologyReport
    {
        $report = $this->repository->findById($id, $organizationId);
        if (! $report) {
            throw new NotFoundException('Radiology report not found.');
        }
        return $report;
    }

    public function create(CreateRadiologyReportDTO $dto): RadiologyReport
    {
        $data = $dto->toArray();

        return DB::transaction(fn (): RadiologyReport => $this->repository->create($data));
    }

    public function update(string $id, UpdateRadiologyReportDTO $dto, string $organizationId): RadiologyReport
    {
        $report = $this->findById($id, $organizationId);

        if ($report->is_final) {
            throw new BusinessException('Cannot update a finalized radiology report.');
        }

        $data = $dto->toArray();

        return DB::transaction(fn (): RadiologyReport => $this->repository->update($report, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $report = $this->findById($id, $organizationId);

        if ($report->is_final) {
            throw new BusinessException('Cannot delete a finalized radiology report.');
        }

        return $this->repository->delete($report);
    }

    public function finalizeReport(string $id, string $organizationId): RadiologyReport
    {
        $report = $this->findById($id, $organizationId);

        if ($report->is_final) {
            throw new BusinessException('Report is already finalized.');
        }

        $data = [
            'is_final'    => true,
            'reviewed_at' => now(),
        ];

        return DB::transaction(fn (): RadiologyReport => $this->repository->update($report, $data));
    }
}
