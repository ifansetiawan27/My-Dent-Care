<?php

declare(strict_types=1);

namespace App\Domains\Patient\Interfaces;

use App\Domains\Patient\DTO\CreatePatientDTO;
use App\Domains\Patient\DTO\UpdatePatientDTO;
use App\Domains\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Patient;
    public function create(CreatePatientDTO $dto): Patient;
    public function update(string $id, UpdatePatientDTO $dto, string $organizationId): Patient;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Patient;
}