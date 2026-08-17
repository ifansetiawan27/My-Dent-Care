<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Laboratory\DTO\CreateLaboratoryDTO;
use App\Domains\Laboratory\DTO\UpdateLaboratoryDTO;
use App\Domains\Laboratory\Enums\LabOrderStatus;
use App\Domains\Laboratory\Interfaces\LaboratoryRepositoryInterface;
use App\Domains\Laboratory\Interfaces\LaboratoryServiceInterface;
use App\Domains\Laboratory\Models\Laboratory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class LaboratoryService implements LaboratoryServiceInterface
{
    public function __construct(
        private readonly LaboratoryRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Laboratory
    {
        $laboratory = $this->repository->findById($id, $organizationId);
        if (! $laboratory) {
            throw new NotFoundException('Lab order not found.');
        }
        return $laboratory;
    }

    public function create(CreateLaboratoryDTO $dto): Laboratory
    {
        return DB::transaction(fn (): Laboratory => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateLaboratoryDTO $dto, string $organizationId): Laboratory
    {
        $laboratory = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                LabOrderStatus::from($laboratory->status),
                LabOrderStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): Laboratory => $this->repository->update($laboratory, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateStatusTransition(LabOrderStatus $current, LabOrderStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update a lab order that is already in a terminal state.');
        }

        $allowed = match ($current) {
            LabOrderStatus::Pending => [LabOrderStatus::InProgress, LabOrderStatus::Cancelled],
            LabOrderStatus::InProgress => [LabOrderStatus::Completed, LabOrderStatus::Cancelled],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition lab order from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}