<?php

declare(strict_types=1);

namespace App\Domains\EMR\Interfaces;

use App\Domains\EMR\DTO\CreateEMRDTO;
use App\Domains\EMR\DTO\UpdateEMRDTO;
use App\Domains\EMR\Models\EMR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EMRServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): EMR;
    public function create(CreateEMRDTO $dto): EMR;
    public function update(string $id, UpdateEMRDTO $dto, string $organizationId): EMR;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): EMR;
}