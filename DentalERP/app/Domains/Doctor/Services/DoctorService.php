<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Doctor\DTO\CreateDoctorDTO;
use App\Domains\Doctor\DTO\UpdateDoctorDTO;
use App\Domains\Doctor\Interfaces\DoctorRepositoryInterface;
use App\Domains\Doctor\Interfaces\DoctorServiceInterface;
use App\Domains\Doctor\Models\Doctor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class DoctorService implements DoctorServiceInterface
{
    public function __construct(
        private readonly DoctorRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Doctor
    {
        $doctor = $this->repository->findById($id, $organizationId);
        if (! $doctor) {
            throw new NotFoundException('Doctor not found.');
        }
        return $doctor;
    }

    public function create(CreateDoctorDTO $dto): Doctor
    {
        if ($this->repository->existsByDoctorCode($dto->doctorCode)) {
            throw new BusinessException('Doctor code already taken.');
        }
        return DB::transaction(fn (): Doctor => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateDoctorDTO $dto, string $organizationId): Doctor
    {
        $doctor = $this->findById($id, $organizationId);
        $data = $dto->toArray();
        if (isset($data['doctor_code']) && $this->repository->existsByDoctorCode($data['doctor_code'], $id)) {
            throw new BusinessException('Doctor code already taken.');
        }
        return DB::transaction(fn (): Doctor => $this->repository->update($doctor, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Doctor
    {
        $doctor = $this->findById($id, $organizationId);
        return $this->repository->update($doctor, ['is_active' => ! $doctor->is_active]);
    }
}