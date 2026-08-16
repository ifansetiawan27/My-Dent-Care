<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Interfaces;

use App\Domains\Doctor\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DoctorRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Doctor;
    public function create(array $data): Doctor;
    public function update(Doctor $doctor, array $data): Doctor;
    public function delete(Doctor $doctor): bool;
    public function existsByDoctorCode(string $code, ?string $excludeId = null): bool;
}