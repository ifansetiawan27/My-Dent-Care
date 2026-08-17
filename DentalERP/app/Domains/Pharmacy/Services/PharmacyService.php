<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Pharmacy\DTO\CreatePharmacyDTO;
use App\Domains\Pharmacy\DTO\UpdatePharmacyDTO;
use App\Domains\Pharmacy\Interfaces\PharmacyRepositoryInterface;
use App\Domains\Pharmacy\Interfaces\PharmacyServiceInterface;
use App\Domains\Pharmacy\Models\Pharmacy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class PharmacyService implements PharmacyServiceInterface
{
    public function __construct(
        private readonly PharmacyRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Pharmacy
    {
        $pharmacy = $this->repository->findById($id, $organizationId);
        if (! $pharmacy) {
            throw new NotFoundException('Pharmacy item not found.');
        }
        return $pharmacy;
    }

    public function create(CreatePharmacyDTO $dto): Pharmacy
    {
        return DB::transaction(function () use ($dto): Pharmacy {
            $this->validateDrugCodeUnique($dto->drugCode, $dto->organizationId);

            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdatePharmacyDTO $dto, string $organizationId): Pharmacy
    {
        $pharmacy = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['drug_code']) && $data['drug_code'] !== $pharmacy->drug_code) {
            $this->validateDrugCodeUnique($data['drug_code'], $organizationId, $id);
        }

        return DB::transaction(fn (): Pharmacy => $this->repository->update($pharmacy, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Pharmacy
    {
        $pharmacy = $this->findById($id, $organizationId);

        return DB::transaction(fn (): Pharmacy => $this->repository->update($pharmacy, [
            'is_active' => ! $pharmacy->is_active,
        ]));
    }

    private function validateDrugCodeUnique(string $drugCode, string $organizationId, ?string $excludeId = null): void
    {
        if ($this->repository->existsByDrugCode($drugCode, $organizationId, $excludeId)) {
            throw new BusinessException('Drug code already exists.');
        }
    }
}