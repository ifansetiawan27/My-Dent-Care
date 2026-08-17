<?php

declare(strict_types=1);

namespace App\Domains\HR\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\HR\DTO\CreateHRDTO;
use App\Domains\HR\DTO\UpdateHRDTO;
use App\Domains\HR\Enums\HRStatus;
use App\Domains\HR\Interfaces\HRRepositoryInterface;
use App\Domains\HR\Interfaces\HRServiceInterface;
use App\Domains\HR\Models\HR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class HRService implements HRServiceInterface
{
    public function __construct(
        private readonly HRRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): HR
    {
        $hr = $this->repository->findById($id, $organizationId);
        if (! $hr) {
            throw new NotFoundException('HR record not found.');
        }
        return $hr;
    }

    public function create(CreateHRDTO $dto): HR
    {
        return DB::transaction(fn (): HR => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateHRDTO $dto, string $organizationId): HR
    {
        $hr = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                HRStatus::from($hr->status),
                HRStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): HR => $this->repository->update($hr, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateStatusTransition(HRStatus $current, HRStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update an HR record that is already archived.');
        }

        $allowed = match ($current) {
            HRStatus::Active => [HRStatus::Inactive, HRStatus::Archived],
            HRStatus::Inactive => [HRStatus::Active, HRStatus::Archived],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition HR record from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}