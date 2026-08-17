<?php

declare(strict_types=1);

namespace App\Domains\Asset\Interfaces;

use App\Domains\Asset\DTO\CreateAssetDTO;
use App\Domains\Asset\DTO\UpdateAssetDTO;
use App\Domains\Asset\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssetServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Asset;
    public function create(CreateAssetDTO $dto): Asset;
    public function update(string $id, UpdateAssetDTO $dto, string $organizationId): Asset;
    public function delete(string $id, string $organizationId): bool;
}