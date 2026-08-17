<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Interfaces;

use App\Domains\Laboratory\DTO\CreateLaboratoryDTO;
use App\Domains\Laboratory\DTO\UpdateLaboratoryDTO;
use App\Domains\Laboratory\Models\Laboratory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LaboratoryServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Laboratory;
    public function create(CreateLaboratoryDTO $dto): Laboratory;
    public function update(string $id, UpdateLaboratoryDTO $dto, string $organizationId): Laboratory;
    public function delete(string $id, string $organizationId): bool;
}