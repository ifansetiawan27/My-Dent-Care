<?php

declare(strict_types=1);

namespace App\Domains\AI\Interfaces;

use App\Domains\AI\DTO\CreateAIDTO;
use App\Domains\AI\Models\AI;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AIServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): AI;
    public function create(CreateAIDTO $dto): AI;
    public function retry(string $id, string $organizationId): AI;
    public function cancel(string $id, string $organizationId): AI;
}