<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Interfaces;

use App\Domains\Procurement\DTO\CreateProcurementDTO;
use App\Domains\Procurement\DTO\UpdateProcurementDTO;
use App\Domains\Procurement\Models\Procurement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProcurementServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Procurement;
    public function create(CreateProcurementDTO $dto): Procurement;
    public function update(string $id, UpdateProcurementDTO $dto, string $organizationId): Procurement;
    public function delete(string $id, string $organizationId): bool;
}