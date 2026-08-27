<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Interfaces;

use App\Domains\Radiology\DTO\CreateRadiologyImageDTO;
use App\Domains\Radiology\Models\RadiologyImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RadiologyImageServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): RadiologyImage;
    public function create(CreateRadiologyImageDTO $dto): RadiologyImage;
    public function update(string $id, array $data, string $organizationId): RadiologyImage;
    public function delete(string $id, string $organizationId): bool;
}
