<?php

declare(strict_types=1);

namespace App\Domains\CRM\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\CRM\DTO\CreateCRMDTO;
use App\Domains\CRM\DTO\UpdateCRMDTO;
use App\Domains\CRM\Enums\CRMStatus;
use App\Domains\CRM\Interfaces\CRMRepositoryInterface;
use App\Domains\CRM\Interfaces\CRMServiceInterface;
use App\Domains\CRM\Models\CRM;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class CRMService implements CRMServiceInterface
{
    public function __construct(
        private readonly CRMRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): CRM
    {
        $crm = $this->repository->findById($id, $organizationId);
        if (! $crm) {
            throw new NotFoundException('CRM contact not found.');
        }
        return $crm;
    }

    public function create(CreateCRMDTO $dto): CRM
    {
        return DB::transaction(fn (): CRM => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateCRMDTO $dto, string $organizationId): CRM
    {
        $crm = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                CRMStatus::from($crm->status),
                CRMStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): CRM => $this->repository->update($crm, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateStatusTransition(CRMStatus $current, CRMStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update a CRM contact that is already closed.');
        }

        $allowed = match ($current) {
            CRMStatus::New => [CRMStatus::InProgress, CRMStatus::Resolved, CRMStatus::Closed],
            CRMStatus::InProgress => [CRMStatus::Resolved, CRMStatus::Closed],
            CRMStatus::Resolved => [CRMStatus::Closed],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition CRM contact from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}