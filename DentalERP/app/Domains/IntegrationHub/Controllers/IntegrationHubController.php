<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Controllers;

use App\Core\Base\BaseController;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Support\ApiResponse;
use App\Domains\IntegrationHub\DTO\CreateIntegrationDTO;
use App\Domains\IntegrationHub\DTO\UpdateIntegrationDTO;
use App\Domains\IntegrationHub\Interfaces\IntegrationHubServiceInterface;
use App\Domains\IntegrationHub\Requests\StoreIntegrationRequest;
use App\Domains\IntegrationHub\Requests\UpdateIntegrationRequest;
use App\Domains\IntegrationHub\Resources\IntegrationHubResource;
use Illuminate\Http\JsonResponse;
use Throwable;

final class IntegrationHubController extends BaseController
{
    public function __construct(
        private readonly IntegrationHubServiceInterface $service,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $paginator = $this->service->paginate([
                'organization_id' => auth()->user()->organization_id,
                ...request()->only(['provider', 'is_active', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
            ]);

            return ApiResponse::paginate(
                paginator: $paginator,
                data:      IntegrationHubResource::collection($paginator),
                message:   'Integration configurations retrieved successfully.',
            );
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $integration = $this->service->findById($id, auth()->user()->organization_id);

            return ApiResponse::success(
                data:    new IntegrationHubResource($integration),
                message: 'Integration configuration retrieved successfully.',
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function store(StoreIntegrationRequest $request): JsonResponse
    {
        try {
            $dto = new CreateIntegrationDTO(
                provider:       $request->validated('provider'),
                name:           $request->validated('name'),
                organizationId: auth()->user()->organization_id,
                config:         $request->validated('config'),
                credentials:    $request->validated('credentials'),
            );

            $integration = $this->service->create($dto);

            return ApiResponse::created(
                data:    new IntegrationHubResource($integration),
                message: 'Integration configuration created successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 422);
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function update(string $id, UpdateIntegrationRequest $request): JsonResponse
    {
        try {
            $dto = new UpdateIntegrationDTO(
                provider:    $request->validated('provider'),
                name:        $request->validated('name'),
                config:      $request->validated('config'),
                credentials: $request->validated('credentials'),
                isActive:    $request->validated('is_active'),
            );

            $integration = $this->service->update($id, $dto, auth()->user()->organization_id);

            return ApiResponse::success(
                data:    new IntegrationHubResource($integration),
                message: 'Integration configuration updated successfully.',
            );
        } catch (BusinessException $e) {
            return ApiResponse::error(message: $e->getMessage(), code: 422);
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->delete($id, auth()->user()->organization_id);

            return ApiResponse::success(
                data:    null,
                message: 'Integration configuration deleted successfully.',
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $integration = $this->service->toggleActive($id, auth()->user()->organization_id);

            return ApiResponse::success(
                data:    new IntegrationHubResource($integration),
                message: 'Integration status toggled successfully.',
            );
        } catch (NotFoundException $e) {
            return ApiResponse::notFound($e->getMessage());
        } catch (Throwable $e) {
            return ApiResponse::serverError($e->getMessage());
        }
    }
}