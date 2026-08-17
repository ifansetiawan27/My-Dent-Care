<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Interfaces;

use App\Domains\Inventory\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Inventory;
    public function create(array $data): Inventory;
    public function update(Inventory $inventory, array $data): Inventory;
    public function delete(Inventory $inventory): bool;
}