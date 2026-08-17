<?php

declare(strict_types=1);

namespace App\Domains\HR\Interfaces;

use App\Domains\HR\Models\HR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HRRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?HR;
    public function create(array $data): HR;
    public function update(HR $hr, array $data): HR;
    public function delete(HR $hr): bool;
}