<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Interfaces;

use App\Domains\Pharmacy\DTO\CreatePharmacyDTO;
use App\Domains\Pharmacy\DTO\UpdatePharmacyDTO;
use App\Domains\Pharmacy\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PharmacyServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Pharmacy;
    public function create(CreatePharmacyDTO $dto): Pharmacy;
    public function update(string $id, UpdatePharmacyDTO $dto, string $organizationId): Pharmacy;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Pharmacy;
}