<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Reporting\DTO\CreateReportingDTO;
use App\Domains\Reporting\DTO\UpdateReportingDTO;
use App\Domains\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domains\Reporting\Interfaces\ReportingServiceInterface;
use App\Domains\Reporting\Models\Reporting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ReportingService implements ReportingServiceInterface
{
    public function __construct(
        private readonly ReportingRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Reporting
    {
        $reporting = $this->repository->findById($id, $organizationId);
        if (! $reporting) {
            throw new NotFoundException('Report not found.');
        }
        return $reporting;
    }

    public function create(CreateReportingDTO $dto): Reporting
    {
        return DB::transaction(fn (): Reporting => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateReportingDTO $dto, string $organizationId): Reporting
    {
        $reporting = $this->findById($id, $organizationId);

        return DB::transaction(fn (): Reporting => $this->repository->update($reporting, $dto->toArray()));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }
}