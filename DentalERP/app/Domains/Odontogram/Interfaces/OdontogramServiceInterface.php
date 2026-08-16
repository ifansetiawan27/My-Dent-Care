<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Interfaces;

use App\Domains\Odontogram\DTO\CreateOdontogramDTO;
use App\Domains\Odontogram\DTO\UpdateOdontogramDTO;
use App\Domains\Odontogram\Models\Odontogram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OdontogramServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Odontogram;
    public function create(CreateOdontogramDTO $dto): Odontogram;
    public function update(string $id, UpdateOdontogramDTO $dto, string $organizationId): Odontogram;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Odontogram;
}