<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Interfaces;

use App\Domains\Treatment\DTO\CreateTreatmentDTO;
use App\Domains\Treatment\DTO\UpdateTreatmentDTO;
use App\Domains\Treatment\Models\Treatment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TreatmentServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Treatment;
    public function create(CreateTreatmentDTO $dto): Treatment;
    public function update(string $id, UpdateTreatmentDTO $dto, string $organizationId): Treatment;
    public function delete(string $id, string $organizationId): bool;
}