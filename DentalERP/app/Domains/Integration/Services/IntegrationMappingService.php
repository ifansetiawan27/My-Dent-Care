<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\DTO\CreateIntegrationMappingDTO;
use App\Domains\Integration\DTO\UpdateIntegrationMappingDTO;
use App\Domains\Integration\Interfaces\IntegrationConfigRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationMappingRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationMappingServiceInterface;
use App\Domains\Integration\Models\IntegrationMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class IntegrationMappingService implements IntegrationMappingServiceInterface
{
    public function __construct(
        private readonly IntegrationMappingRepositoryInterface $repository,
        private readonly IntegrationConfigRepositoryInterface $configRepository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): IntegrationMapping
    {
        $mapping = $this->repository->findById($id, $organizationId);
        if (! $mapping) {
            throw new NotFoundException('Integration mapping not found.');
        }
        return $mapping;
    }

    public function create(CreateIntegrationMappingDTO $dto): IntegrationMapping
    {
        // Verify the config exists and belongs to the organization
        $config = $this->configRepository->findById($dto->integrationConfigId, auth()->user()->organization_id);
        if (! $config) {
            throw new NotFoundException('Integration configuration not found.');
        }

        // Check for duplicate mapping
        $existing = $this->repository->findByExternalCode(
            $dto->integrationConfigId,
            $dto->localType,
            $dto->localId,
        );
        if ($existing) {
            throw new BusinessException('A mapping already exists for this local type and ID.');
        }

        $data = $dto->toArray();

        return DB::transaction(fn (): IntegrationMapping => $this->repository->create($data));
    }

    public function update(string $id, UpdateIntegrationMappingDTO $dto, string $organizationId): IntegrationMapping
    {
        $mapping = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        return DB::transaction(fn (): IntegrationMapping => $this->repository->update($mapping, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $mapping = $this->findById($id, $organizationId);

        return $this->repository->delete($mapping);
    }

    public function findByExternalCode(string $integrationConfigId, string $localType, string $externalCode): ?IntegrationMapping
    {
        return $this->repository->findByExternalCode($integrationConfigId, $localType, $externalCode);
    }
}
