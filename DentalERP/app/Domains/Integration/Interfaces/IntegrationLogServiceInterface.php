<?php

declare(strict_types=1);

namespace App\Domains\Integration\Interfaces;

use App\Domains\Integration\DTO\CreateIntegrationLogDTO;
use App\Domains\Integration\Models\IntegrationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IntegrationLogServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id): IntegrationLog;
    public function logIntegration(CreateIntegrationLogDTO $dto): IntegrationLog;
}
