<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyImageDTO;
use App\Domains\Radiology\Interfaces\RadiologyImageRepositoryInterface;
use App\Domains\Radiology\Interfaces\RadiologyImageServiceInterface;
use App\Domains\Radiology\Models\RadiologyImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class RadiologyImageService implements RadiologyImageServiceInterface
{
    public function __construct(
        private readonly RadiologyImageRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): RadiologyImage
    {
        $image = $this->repository->findById($id, $organizationId);
        if (! $image) {
            throw new NotFoundException('Radiology image not found.');
        }
        return $image;
    }

    public function create(CreateRadiologyImageDTO $dto): RadiologyImage
    {
        $data = $dto->toArray();

        return DB::transaction(fn (): RadiologyImage => $this->repository->create($data));
    }

    public function update(string $id, array $data, string $organizationId): RadiologyImage
    {
        $image = $this->findById($id, $organizationId);

        return DB::transaction(fn (): RadiologyImage => $this->repository->update($image, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $image = $this->findById($id, $organizationId);

        return $this->repository->delete($image);
    }
}
