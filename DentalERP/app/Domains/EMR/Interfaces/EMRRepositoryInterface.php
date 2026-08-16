<?php

declare(strict_types=1);

namespace App\Domains\EMR\Interfaces;

use App\Domains\EMR\Models\EMR;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EMRRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?EMR;
    public function create(array $data): EMR;
    public function update(EMR $emr, array $data): EMR;
    public function delete(EMR $emr): bool;
    public function existsByCode(string $code, ?string $excludeId = null): bool;
}