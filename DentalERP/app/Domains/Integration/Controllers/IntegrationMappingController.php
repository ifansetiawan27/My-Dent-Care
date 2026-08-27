<?php

declare(strict_types=1);

namespace App\Domains\Integration\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\DTO\CreateIntegrationMappingDTO;
use App\Domains\Integration\DTO\UpdateIntegrationMappingDTO;
use App\Domains\Integration\Interfaces\IntegrationMappingServiceInterface;
use App\Domains\Integration\Requests\StoreIntegrationMappingRequest;
use App\Domains\Integration\Requests\UpdateIntegrationMappingRequest;
use App\Domains\Integration\Resources\IntegrationMappingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class IntegrationMappingController extends Controller
{
    public function __construct(
        private readonly IntegrationMappingServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return IntegrationMappingResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['integration_config_id', 'local_type', 'is_synced', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new IntegrationMappingResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Integration mapping not found.'], 404);
        }
    }

    public function store(StoreIntegrationMappingRequest $r): JsonResponse
    {
        try {
            $dto = new CreateIntegrationMappingDTO(
                integrationConfigId: $r->validated('integration_config_id'),
                localType: $r->validated('local_type'),
                localId: $r->validated('local_id'),
                externalCode: $r->validated('external_code'),
                isSynced: $r->validated('is_synced', false),
                externalData: $r->validated('external_data'),
            );
            return (new IntegrationMappingResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateIntegrationMappingRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateIntegrationMappingDTO(
                localType: $r->validated('local_type'),
                localId: $r->validated('local_id'),
                externalCode: $r->validated('external_code'),
                isSynced: $r->validated('is_synced'),
                externalData: $r->validated('external_data'),
            );
            return (new IntegrationMappingResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->delete($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function findByExternal(string $type, string $code): JsonResponse
    {
        $integrationConfigId = request()->query('integration_config_id');
        if (! $integrationConfigId) {
            return response()->json(['success' => false, 'message' => 'integration_config_id query parameter is required.'], 400);
        }

        $mapping = $this->svc->findByExternalCode($integrationConfigId, $type, $code);
        if (! $mapping) {
            return response()->json(['success' => false, 'message' => 'Mapping not found.'], 404);
        }

        return (new IntegrationMappingResource($mapping))->response();
    }
}
