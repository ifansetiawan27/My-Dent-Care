<?php

declare(strict_types=1);

namespace App\Domains\Patient\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Patient\DTO\CreatePatientDTO;
use App\Domains\Patient\DTO\UpdatePatientDTO;
use App\Domains\Patient\Interfaces\PatientRepositoryInterface;
use App\Domains\Patient\Interfaces\PatientServiceInterface;
use App\Domains\Patient\Models\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class PatientService implements PatientServiceInterface
{
    public function __construct(
        private readonly PatientRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Patient
    {
        $patient = $this->repository->findById($id, $organizationId);
        if (! $patient) {
            throw new NotFoundException('Patient not found.');
        }
        return $patient;
    }

    public function create(CreatePatientDTO $dto): Patient
    {
        if ($this->repository->existsByPatientCode($dto->patientCode)) {
            throw new BusinessException('Patient code already taken.');
        }
        return DB::transaction(fn (): Patient => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdatePatientDTO $dto, string $organizationId): Patient
    {
        $patient = $this->findById($id, $organizationId);
        $data = $dto->toArray();
        if (isset($data['patient_code']) && $this->repository->existsByPatientCode($data['patient_code'], $id)) {
            throw new BusinessException('Patient code already taken.');
        }
        return DB::transaction(fn (): Patient => $this->repository->update($patient, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Patient
    {
        $patient = $this->findById($id, $organizationId);
        return $this->repository->update($patient, ['is_active' => ! $patient->is_active]);
    }
}