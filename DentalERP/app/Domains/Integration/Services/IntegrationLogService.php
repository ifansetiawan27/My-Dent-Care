<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\DTO\CreateIntegrationLogDTO;
use App\Domains\Integration\Interfaces\IntegrationLogRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationLogServiceInterface;
use App\Domains\Integration\Models\IntegrationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class IntegrationLogService implements IntegrationLogServiceInterface
{
    public function __construct(
        private readonly IntegrationLogRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id): IntegrationLog
    {
        $log = $this->repository->findById($id);
        if (! $log) {
            throw new NotFoundException('Integration log not found.');
        }
        return $log;
    }

    public function logIntegration(CreateIntegrationLogDTO $dto): IntegrationLog
    {
        $data = $dto->toArray();

        return DB::transaction(fn (): IntegrationLog => $this->repository->create($data));
    }
}
