<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\DTO\CreateRadiologyOrderDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyOrderDTO;
use App\Domains\Radiology\Models\RadiologyOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyOrderServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): RadiologyOrder;
    public function create(CreateRadiologyOrderDTO $dto): RadiologyOrder;
    public function update(string $id, UpdateRadiologyOrderDTO $dto, string $organizationId): RadiologyOrder;
    public function delete(string $id, string $organizationId): bool;
    public function completeOrder(string $id, string $organizationId): RadiologyOrder;
}
