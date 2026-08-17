<?php

declare(strict_types=1);

namespace App\Domains\AI\Interfaces;

use App\Domains\AI\Models\AI;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AIRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?AI;
    public function create(array $data): AI;
    public function update(AI $ai, array $data): AI;
}