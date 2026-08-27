<?php

declare(strict_types=1);

namespace App\Domains\Integration\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\DTO\CreateIntegrationConfigDTO;
use App\Domains\Integration\DTO\UpdateIntegrationConfigDTO;
use App\Domains\Integration\Interfaces\IntegrationConfigRepositoryInterface;
use App\Domains\Integration\Interfaces\IntegrationConfigServiceInterface;
use App\Domains\Integration\Models\IntegrationConfig;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class IntegrationConfigService implements IntegrationConfigServiceInterface
{
    public function __construct(
        private readonly IntegrationConfigRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): IntegrationConfig
    {
        $config = $this->repository->findById($id, $organizationId);
        if (! $config) {
            throw new NotFoundException('Integration configuration not found.');
        }
        return $config;
    }

    public function create(CreateIntegrationConfigDTO $dto): IntegrationConfig
    {
        $data = $dto->toArray();

        return DB::transaction(fn (): IntegrationConfig => $this->repository->create($data));
    }

    public function update(string $id, UpdateIntegrationConfigDTO $dto, string $organizationId): IntegrationConfig
    {
        $config = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        return DB::transaction(fn (): IntegrationConfig => $this->repository->update($config, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $config = $this->findById($id, $organizationId);

        return $this->repository->delete($config);
    }

    public function testConnection(string $id, string $organizationId): array
    {
        $config = $this->findById($id, $organizationId);

        if (empty($config->endpoint_url)) {
            throw new BusinessException('Endpoint URL is not configured.');
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get($config->endpoint_url);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'message' => $response->successful()
                    ? 'Connection successful.'
                    : "Connection failed with status {$response->status()}.",
            ];
        } catch (\Exception $e) {
            throw new BusinessException("Connection test failed: {$e->getMessage()}");
        }
    }
}
