<?php

declare(strict_types=1);

namespace App\Domains\Asset\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Asset\DTO\CreateAssetDTO;
use App\Domains\Asset\DTO\UpdateAssetDTO;
use App\Domains\Asset\Interfaces\AssetServiceInterface;
use App\Domains\Asset\Requests\StoreAssetRequest;
use App\Domains\Asset\Requests\UpdateAssetRequest;
use App\Domains\Asset\Resources\AssetResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AssetController extends Controller
{
    public function __construct(
        private readonly AssetServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return AssetResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['category_id', 'status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new AssetResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Asset not found.'], 404);
        }
    }

    public function store(StoreAssetRequest $r): JsonResponse
    {
        try {
            $dto = new CreateAssetDTO(
                assetCode: $r->validated('asset_code'),
                name: $r->validated('name'),
                organizationId: auth()->user()->organization_id,
                branchId: $r->validated('branch_id'),
                categoryId: $r->validated('category_id'),
                description: $r->validated('description'),
                purchaseDate: $r->validated('purchase_date'),
                purchasePrice: $r->validated('purchase_price'),
                warrantyExpiry: $r->validated('warranty_expiry'),
                notes: $r->validated('notes'),
            );
            return (new AssetResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateAssetRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateAssetDTO(
                assetCode: $r->validated('asset_code'),
                name: $r->validated('name'),
                status: $r->validated('status'),
                branchId: $r->validated('branch_id'),
                categoryId: $r->validated('category_id'),
                description: $r->validated('description'),
                purchaseDate: $r->validated('purchase_date'),
                purchasePrice: $r->validated('purchase_price'),
                warrantyExpiry: $r->validated('warranty_expiry'),
                notes: $r->validated('notes'),
            );
            return (new AssetResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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