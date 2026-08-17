<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\AI\DTO\CreateAIDTO;
use App\Domains\AI\Enums\AIStatus;
use App\Domains\AI\Interfaces\AIRepositoryInterface;
use App\Domains\AI\Interfaces\AIServiceInterface;
use App\Domains\AI\Models\AI;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AIService implements AIServiceInterface
{
    public function __construct(
        private readonly AIRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): AI
    {
        $ai = $this->repository->findById($id, $organizationId);
        if (! $ai) {
            throw new NotFoundException('AI query not found.');
        }
        return $ai;
    }

    public function create(CreateAIDTO $dto): AI
    {
        return DB::transaction(fn (): AI => $this->repository->create($dto->toArray()));
    }

    public function retry(string $id, string $organizationId): AI
    {
        $ai = $this->findById($id, $organizationId);

        if ($ai->status !== AIStatus::Failed->value) {
            throw new BusinessException('Only failed queries can be retried.');
        }

        return DB::transaction(fn (): AI => $this->repository->update($ai, [
            'status'        => AIStatus::Pending->value,
            'error_message' => null,
        ]));
    }

    public function cancel(string $id, string $organizationId): AI
    {
        $ai = $this->findById($id, $organizationId);

        $status = AIStatus::from($ai->status);
        if (! $status->isCancellable()) {
            throw new BusinessException('Only pending or processing queries can be cancelled.');
        }

        return DB::transaction(fn (): AI => $this->repository->update($ai, [
            'status'        => AIStatus::Failed->value,
            'error_message' => 'Cancelled by user',
        ]));
    }
}