<?php

declare(strict_types=1);

namespace App\Domains\HR\Interfaces;

use App\Domains\HR\DTO\CreateHRDTO;
use App\Domains\HR\DTO\UpdateHRDTO;
use App\Domains\HR\Models\HR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HRServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): HR;
    public function create(CreateHRDTO $dto): HR;
    public function update(string $id, UpdateHRDTO $dto, string $organizationId): HR;
    public function delete(string $id, string $organizationId): bool;
}