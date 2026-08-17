<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Interfaces;

use App\Domains\Pharmacy\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PharmacyRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Pharmacy;
    public function create(array $data): Pharmacy;
    public function update(Pharmacy $pharmacy, array $data): Pharmacy;
    public function delete(Pharmacy $pharmacy): bool;
    public function existsByDrugCode(string $drugCode, string $organizationId, ?string $excludeId = null): bool;
}