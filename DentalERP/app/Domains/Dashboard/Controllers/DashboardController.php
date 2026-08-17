<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Dashboard\DTO\CreateDashboardDTO;
use App\Domains\Dashboard\DTO\UpdateDashboardDTO;
use App\Domains\Dashboard\Interfaces\DashboardServiceInterface;
use App\Domains\Dashboard\Requests\StoreDashboardRequest;
use App\Domains\Dashboard\Requests\UpdateDashboardRequest;
use App\Domains\Dashboard\Resources\DashboardResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return DashboardResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['user_id', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new DashboardResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Dashboard not found.'], 404);
        }
    }

    public function store(StoreDashboardRequest $r): JsonResponse
    {
        try {
            $dto = new CreateDashboardDTO(
                name: $r->validated('name'),
                organizationId: auth()->user()->organization_id,
                userId: $r->validated('user_id'),
                config: $r->validated('config'),
                widgets: $r->validated('widgets'),
                isDefault: $r->validated('is_default'),
            );
            return (new DashboardResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateDashboardRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateDashboardDTO(
                name: $r->validated('name'),
                userId: $r->validated('user_id'),
                config: $r->validated('config'),
                widgets: $r->validated('widgets'),
                isDefault: $r->validated('is_default'),
            );
            return (new DashboardResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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
}