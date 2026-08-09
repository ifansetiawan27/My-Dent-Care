<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Controllers;

use App\Domains\MasterData\DTO\MasterDataFilterDTO;
use App\Domains\MasterData\Helpers\ResourceResolver;
use App\Domains\MasterData\Interfaces\MasterDataServiceInterface;
use App\Domains\MasterData\Requests\MasterDataStoreRequest;
use App\Domains\MasterData\Requests\MasterDataUpdateRequest;
use App\Domains\MasterData\Resources\MasterDataResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class MasterDataController extends Controller
{
    public function __construct(
        private readonly ResourceResolver $resolver,
    ) {}

    public function index(string $resource): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);
        $filter  = MasterDataFilterDTO::fromRequest(request());
        $records = $service->paginate($filter);

        return MasterDataResource::collection($records)->response();
    }

    public function show(string $resource, string $id): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);
        $record  = $service->findById($id);

        return (new MasterDataResource($record))->response();
    }

    public function store(string $resource, MasterDataStoreRequest $request): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);
        $record  = $service->create($request->validated());

        return (new MasterDataResource($record))->response()->setStatusCode(201);
    }

    public function update(string $resource, string $id, MasterDataUpdateRequest $request): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);
        $record  = $service->update($id, $request->validated());

        return (new MasterDataResource($record))->response();
    }

    public function destroy(string $resource, string $id): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);

        $resourceName = $this->resolver->getResourceName($resource);
        $parentColumn = $this->resolver->getParentColumn($resource);

        if ($parentColumn !== null) {
            $repo = $this->resolver->resolveRepository($resourceName);
            $count = $repo->countByParent($parentColumn, $id);

            if ($count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete — this record is referenced by {$count} child records.",
                    'errors'  => [],
                ], 409);
            }
        }

        $service->delete($id);

        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $resource, string $id): JsonResponse
    {
        $service = $this->resolver->resolveService($resource);
        $record  = $service->toggleActive($id);

        return (new MasterDataResource($record))->response();
    }
}
