<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Procurement\DTO\CreateProcurementDTO;
use App\Domains\Procurement\DTO\UpdateProcurementDTO;
use App\Domains\Procurement\Enums\ProcurementStatus;
use App\Domains\Procurement\Interfaces\ProcurementRepositoryInterface;
use App\Domains\Procurement\Interfaces\ProcurementServiceInterface;
use App\Domains\Procurement\Models\Procurement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ProcurementService implements ProcurementServiceInterface
{
    public function __construct(
        private readonly ProcurementRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Procurement
    {
        $procurement = $this->repository->findById($id, $organizationId);
        if (! $procurement) {
            throw new NotFoundException('Procurement order not found.');
        }
        return $procurement;
    }

    public function create(CreateProcurementDTO $dto): Procurement
    {
        return DB::transaction(function () use ($dto): Procurement {
            $this->validateOrderNumberUnique($dto->orderNumber, $dto->organizationId);

            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateProcurementDTO $dto, string $organizationId): Procurement
    {
        $procurement = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['order_number']) && $data['order_number'] !== $procurement->order_number) {
            $this->validateOrderNumberUnique($data['order_number'], $organizationId, $id);
        }

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                ProcurementStatus::from($procurement->status),
                ProcurementStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): Procurement => $this->repository->update($procurement, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateOrderNumberUnique(string $orderNumber, string $organizationId, ?string $excludeId = null): void
    {
        $query = Procurement::where('order_number', $orderNumber)
            ->where('organization_id', $organizationId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new BusinessException('Order number already exists.');
        }
    }

    private function validateStatusTransition(ProcurementStatus $current, ProcurementStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update a procurement order that is already in a terminal state.');
        }

        $allowed = match ($current) {
            ProcurementStatus::Pending => [ProcurementStatus::Approved, ProcurementStatus::Cancelled],
            ProcurementStatus::Approved => [ProcurementStatus::Ordered, ProcurementStatus::Cancelled],
            ProcurementStatus::Ordered => [ProcurementStatus::Received, ProcurementStatus::Cancelled],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition procurement order from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}