<?php

declare(strict_types=1);

namespace App\Domains\EMR\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\EMR\DTO\CreateEMRDTO;
use App\Domains\EMR\DTO\UpdateEMRDTO;
use App\Domains\EMR\Interfaces\EMRRepositoryInterface;
use App\Domains\EMR\Interfaces\EMRServiceInterface;
use App\Domains\EMR\Models\EMR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class EMRService implements EMRServiceInterface
{
    public function __construct(
        private readonly EMRRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): EMR
    {
        $emr = $this->repository->findById($id, $organizationId);
        if (! $emr) {
            throw new NotFoundException('EMR not found.');
        }
        return $emr;
    }

    public function create(CreateEMRDTO $dto): EMR
    {
        return DB::transaction(fn (): EMR => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateEMRDTO $dto, string $organizationId): EMR
    {
        $emr = $this->findById($id, $organizationId);
        return DB::transaction(fn (): EMR => $this->repository->update($emr, $dto->toArray()));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): EMR
    {
        $emr = $this->findById($id, $organizationId);
        $newStatus = $emr->status === 'completed' ? 'open' : 'completed';
        return $this->repository->update($emr, ['status' => $newStatus]);
    }
}