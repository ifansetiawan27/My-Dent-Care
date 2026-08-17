<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Interfaces;

use App\Domains\Procurement\Models\Procurement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProcurementRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Procurement;
    public function create(array $data): Procurement;
    public function update(Procurement $procurement, array $data): Procurement;
    public function delete(Procurement $procurement): bool;
}