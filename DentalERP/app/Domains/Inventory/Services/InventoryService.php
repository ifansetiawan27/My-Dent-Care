<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Inventory\DTO\CreateInventoryDTO;
use App\Domains\Inventory\DTO\UpdateInventoryDTO;
use App\Domains\Inventory\Interfaces\InventoryRepositoryInterface;
use App\Domains\Inventory\Interfaces\InventoryServiceInterface;
use App\Domains\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class InventoryService implements InventoryServiceInterface
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Inventory
    {
        $inventory = $this->repository->findById($id, $organizationId);
        if (! $inventory) {
            throw new NotFoundException('Inventory item not found.');
        }
        return $inventory;
    }

    public function create(CreateInventoryDTO $dto): Inventory
    {
        return DB::transaction(function () use ($dto): Inventory {
            $this->validateItemCodeUnique($dto->itemCode, $dto->organizationId);

            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateInventoryDTO $dto, string $organizationId): Inventory
    {
        $inventory = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['item_code']) && $data['item_code'] !== $inventory->item_code) {
            $this->validateItemCodeUnique($data['item_code'], $organizationId, $id);
        }

        return DB::transaction(fn (): Inventory => $this->repository->update($inventory, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Inventory
    {
        $inventory = $this->findById($id, $organizationId);

        return DB::transaction(fn (): Inventory => $this->repository->update($inventory, [
            'is_active' => ! $inventory->is_active,
        ]));
    }

    private function validateItemCodeUnique(string $itemCode, string $organizationId, ?string $excludeId = null): void
    {
        $query = Inventory::where('item_code', $itemCode)
            ->where('organization_id', $organizationId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new BusinessException('Item code already exists.');
        }
    }
}