<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Interfaces;

use App\Domains\Treatment\Models\Treatment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TreatmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Treatment;
    public function create(array $data): Treatment;
    public function update(Treatment $treatment, array $data): Treatment;
    public function delete(Treatment $treatment): bool;
}