<?php

declare(strict_types=1);

namespace App\Domains\Asset\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Asset\DTO\CreateAssetDTO;
use App\Domains\Asset\DTO\UpdateAssetDTO;
use App\Domains\Asset\Enums\AssetStatus;
use App\Domains\Asset\Interfaces\AssetRepositoryInterface;
use App\Domains\Asset\Interfaces\AssetServiceInterface;
use App\Domains\Asset\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AssetService implements AssetServiceInterface
{
    public function __construct(
        private readonly AssetRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Asset
    {
        $asset = $this->repository->findById($id, $organizationId);
        if (! $asset) {
            throw new NotFoundException('Asset not found.');
        }
        return $asset;
    }

    public function create(CreateAssetDTO $dto): Asset
    {
        return DB::transaction(function () use ($dto): Asset {
            $this->validateAssetCodeUnique($dto->assetCode, $dto->organizationId);

            return $this->repository->create($dto->toArray());
        });
    }

    public function update(string $id, UpdateAssetDTO $dto, string $organizationId): Asset
    {
        $asset = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['asset_code']) && $data['asset_code'] !== $asset->asset_code) {
            $this->validateAssetCodeUnique($data['asset_code'], $organizationId, $id);
        }

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                AssetStatus::from($asset->status),
                AssetStatus::from($data['status']),
            );
        }

        return DB::transaction(fn (): Asset => $this->repository->update($asset, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    private function validateAssetCodeUnique(string $assetCode, string $organizationId, ?string $excludeId = null): void
    {
        $query = Asset::where('asset_code', $assetCode)
            ->where('organization_id', $organizationId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new BusinessException('Asset code already exists.');
        }
    }

    private function validateStatusTransition(AssetStatus $current, AssetStatus $new): void
    {
        if ($current->isTerminal()) {
            throw new BusinessException('Cannot update an asset that is already in a terminal state.');
        }

        $allowed = match ($current) {
            AssetStatus::Active => [AssetStatus::Maintenance, AssetStatus::Retired, AssetStatus::Disposed],
            AssetStatus::Maintenance => [AssetStatus::Active, AssetStatus::Retired, AssetStatus::Disposed],
            AssetStatus::Retired => [AssetStatus::Disposed],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition asset from '{$current->value}' to '{$new->value}'.",
            );
        }
    }
}