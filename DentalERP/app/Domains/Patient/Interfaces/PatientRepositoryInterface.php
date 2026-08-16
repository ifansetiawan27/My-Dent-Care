<?php

declare(strict_types=1);

namespace App\Domains\Patient\Interfaces;

use App\Domains\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PatientRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Patient;
    public function create(array $data): Patient;
    public function update(Patient $patient, array $data): Patient;
    public function delete(Patient $patient): bool;
    public function existsByPatientCode(string $code, ?string $excludeId = null): bool;
}