<?php

declare(strict_types=1);

namespace App\Domains\Asset\Interfaces;

use App\Domains\Asset\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssetRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Asset;
    public function create(array $data): Asset;
    public function update(Asset $asset, array $data): Asset;
    public function delete(Asset $asset): bool;
}