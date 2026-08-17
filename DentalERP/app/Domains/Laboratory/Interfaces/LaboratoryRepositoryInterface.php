<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Interfaces;

use App\Domains\Laboratory\Models\Laboratory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LaboratoryRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Laboratory;
    public function create(array $data): Laboratory;
    public function update(Laboratory $laboratory, array $data): Laboratory;
    public function delete(Laboratory $laboratory): bool;
}