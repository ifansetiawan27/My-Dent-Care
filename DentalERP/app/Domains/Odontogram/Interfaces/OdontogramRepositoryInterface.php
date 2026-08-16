<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Interfaces;

use App\Domains\Odontogram\Models\Odontogram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OdontogramRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Odontogram;
    public function create(array $data): Odontogram;
    public function update(Odontogram $odontogram, array $data): Odontogram;
    public function delete(Odontogram $odontogram): bool;
    public function existsByCode(string $code, ?string $excludeId = null): bool;
}