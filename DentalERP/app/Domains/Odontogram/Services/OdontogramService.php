<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Odontogram\DTO\CreateOdontogramDTO;
use App\Domains\Odontogram\DTO\UpdateOdontogramDTO;
use App\Domains\Odontogram\Interfaces\OdontogramRepositoryInterface;
use App\Domains\Odontogram\Interfaces\OdontogramServiceInterface;
use App\Domains\Odontogram\Models\Odontogram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class OdontogramService implements OdontogramServiceInterface
{
    public function __construct(
        private readonly OdontogramRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Odontogram
    {
        $odontogram = $this->repository->findById($id, $organizationId);
        if (! $odontogram) {
            throw new NotFoundException('Odontogram not found.');
        }
        return $odontogram;
    }

    public function create(CreateOdontogramDTO $dto): Odontogram
    {
        return DB::transaction(fn (): Odontogram => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateOdontogramDTO $dto, string $organizationId): Odontogram
    {
        $odontogram = $this->findById($id, $organizationId);
        return DB::transaction(fn (): Odontogram => $this->repository->update($odontogram, $dto->toArray()));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Odontogram
    {
        $odontogram = $this->findById($id, $organizationId);
        $newCondition = $odontogram->condition === 'healthy' ? 'caries' : 'healthy';
        return $this->repository->update($odontogram, ['condition' => $newCondition]);
    }
}