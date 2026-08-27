<?php

declare(strict_types=1);

namespace App\Domains\Integration\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\DTO\CreateIntegrationConfigDTO;
use App\Domains\Integration\DTO\UpdateIntegrationConfigDTO;
use App\Domains\Integration\Interfaces\IntegrationConfigServiceInterface;
use App\Domains\Integration\Requests\StoreIntegrationConfigRequest;
use App\Domains\Integration\Requests\UpdateIntegrationConfigRequest;
use App\Domains\Integration\Resources\IntegrationConfigResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class IntegrationConfigController extends Controller
{
    public function __construct(
        private readonly IntegrationConfigServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return IntegrationConfigResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['integration_type', 'is_active', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new IntegrationConfigResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Integration configuration not found.'], 404);
        }
    }

    public function store(StoreIntegrationConfigRequest $r): JsonResponse
    {
        try {
            $dto = new CreateIntegrationConfigDTO(
                integrationType: $r->validated('integration_type'),
                name: $r->validated('name'),
                organizationId: auth()->user()->organization_id,
                isActive: $r->validated('is_active', false),
                endpointUrl: $r->validated('endpoint_url'),
                apiKey: $r->validated('api_key'),
                apiSecret: $r->validated('api_secret'),
                config: $r->validated('config'),
            );
            return (new IntegrationConfigResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateIntegrationConfigRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateIntegrationConfigDTO(
                integrationType: $r->validated('integration_type'),
                name: $r->validated('name'),
                isActive: $r->validated('is_active'),
                endpointUrl: $r->validated('endpoint_url'),
                apiKey: $r->validated('api_key'),
                apiSecret: $r->validated('api_secret'),
                config: $r->validated('config'),
            );
            return (new IntegrationConfigResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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

    public function testConnection(string $id): JsonResponse
    {
        try {
            $result = $this->svc->testConnection($id, auth()->user()->organization_id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
