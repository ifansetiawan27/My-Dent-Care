<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\IntegrationHub\DTO\CreateIntegrationDTO;
use App\Domains\IntegrationHub\DTO\UpdateIntegrationDTO;
use App\Domains\IntegrationHub\Interfaces\IntegrationHubRepositoryInterface;
use App\Domains\IntegrationHub\Interfaces\IntegrationHubServiceInterface;
use App\Domains\IntegrationHub\Models\IntegrationHub;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class IntegrationHubService implements IntegrationHubServiceInterface
{
    private const SERVICE_NAME = 'IntegrationHubService';

    public function __construct(
        private readonly IntegrationHubRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): IntegrationHub
    {
        $integration = $this->repository->findById($id, $organizationId);

        if ($integration === null) {
            throw new NotFoundException('Integration configuration not found.');
        }

        return $integration;
    }

    public function create(CreateIntegrationDTO $dto): IntegrationHub
    {
        return DB::transaction(function () use ($dto): IntegrationHub {
            $this->validateProviderUnique($dto->provider, $dto->organizationId);

            $data = $dto->toArray();

            $integration = $this->repository->create($data);

            $this->logInfo('create', 'Integration configuration created.', [
                'id'       => $integration->id,
                'provider' => $integration->provider,
                'org_id'   => $integration->organization_id,
            ]);

            return $integration;
        });
    }

    public function update(string $id, UpdateIntegrationDTO $dto, string $organizationId): IntegrationHub
    {
        $integration = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['provider']) && $data['provider'] !== $integration->provider) {
            $this->validateProviderUnique($data['provider'], $organizationId, $id);
        }

        if (isset($data['credentials'])) {
            $data['credentials'] = $data['credentials'];
        }

        return DB::transaction(function () use ($integration, $data): IntegrationHub {
            $updated = $this->repository->update($integration, $data);

            $this->logInfo('update', 'Integration configuration updated.', [
                'id'       => $updated->id,
                'provider' => $updated->provider,
            ]);

            return $updated;
        });
    }

    public function delete(string $id, string $organizationId): bool
    {
        $integration = $this->findById($id, $organizationId);

        return DB::transaction(function () use ($integration): bool {
            $result = $this->repository->delete($integration);

            $this->logInfo('delete', 'Integration configuration deleted.', [
                'id'       => $integration->id,
                'provider' => $integration->provider,
            ]);

            return $result;
        });
    }

    public function toggleActive(string $id, string $organizationId): IntegrationHub
    {
        $integration = $this->findById($id, $organizationId);

        return DB::transaction(function () use ($integration): IntegrationHub {
            $updated = $this->repository->update($integration, [
                'is_active' => ! $integration->is_active,
            ]);

            $this->logInfo('toggleActive', 'Integration active status toggled.', [
                'id'        => $updated->id,
                'provider'  => $updated->provider,
                'is_active' => $updated->is_active,
            ]);

            return $updated;
        });
    }

    private function validateProviderUnique(string $provider, string $organizationId, ?string $excludeId = null): void
    {
        $existing = $this->repository->findByProvider($provider, $organizationId, $excludeId);

        if ($existing !== null) {
            throw new BusinessException('Provider already exists for this organization.');
        }
    }

    private function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    private function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $e->getMessage(),
            [
                'service'   => self::SERVICE_NAME,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}