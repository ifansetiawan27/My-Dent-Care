<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Interfaces;

use App\Domains\Inventory\DTO\CreateInventoryDTO;
use App\Domains\Inventory\DTO\UpdateInventoryDTO;
use App\Domains\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Inventory;
    public function create(CreateInventoryDTO $dto): Inventory;
    public function update(string $id, UpdateInventoryDTO $dto, string $organizationId): Inventory;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Inventory;
}