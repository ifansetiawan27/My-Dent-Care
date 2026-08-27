<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\Models\RadiologyImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyImageRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?RadiologyImage;
    public function create(array $data): RadiologyImage;
    public function update(RadiologyImage $image, array $data): RadiologyImage;
    public function delete(RadiologyImage $image): bool;
}
