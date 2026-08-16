<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Interfaces;

use App\Domains\Doctor\DTO\CreateDoctorDTO;
use App\Domains\Doctor\DTO\UpdateDoctorDTO;
use App\Domains\Doctor\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DoctorServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Doctor;
    public function create(CreateDoctorDTO $dto): Doctor;
    public function update(string $id, UpdateDoctorDTO $dto, string $organizationId): Doctor;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Doctor;
}