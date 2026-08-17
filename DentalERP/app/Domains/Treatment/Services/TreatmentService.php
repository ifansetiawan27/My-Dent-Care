<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Treatment\DTO\CreateTreatmentDTO;
use App\Domains\Treatment\DTO\UpdateTreatmentDTO;
use App\Domains\Treatment\Enums\TreatmentStatus;
use App\Domains\Treatment\Interfaces\TreatmentRepositoryInterface;
use App\Domains\Treatment\Interfaces\TreatmentServiceInterface;
use App\Domains\Treatment\Models\Treatment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class TreatmentService implements TreatmentServiceInterface
{
    public function __construct(
        private readonly TreatmentRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Treatment
    {
        $treatment = $this->repository->findById($id, $organizationId);
        if (! $treatment) {
            throw new NotFoundException('Treatment not found.');
        }
        return $treatment;
    }

    public function create(CreateTreatmentDTO $dto): Treatment
    {
        return DB::transaction(fn (): Treatment => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateTreatmentDTO $dto, string $organizationId): Treatment
    {
        $treatment = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                TreatmentStatus::from($treatment->status),
                TreatmentStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): Treatment => $this->repository->update($treatment, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateStatusTransition(TreatmentStatus $current, TreatmentStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update a treatment that is already in a terminal state.');
        }

        $allowed = match ($current) {
            TreatmentStatus::Planned => [TreatmentStatus::InProgress, TreatmentStatus::Cancelled],
            TreatmentStatus::InProgress => [TreatmentStatus::Completed, TreatmentStatus::Cancelled],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition treatment from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}