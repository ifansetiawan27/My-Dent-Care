<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Inventory\DTO\CreateInventoryDTO;
use App\Domains\Inventory\DTO\UpdateInventoryDTO;
use App\Domains\Inventory\Interfaces\InventoryServiceInterface;
use App\Domains\Inventory\Requests\StoreInventoryRequest;
use App\Domains\Inventory\Requests\UpdateInventoryRequest;
use App\Domains\Inventory\Resources\InventoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return InventoryResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['branch_id', 'category_id', 'is_active', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new InventoryResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Inventory item not found.'], 404);
        }
    }

    public function store(StoreInventoryRequest $r): JsonResponse
    {
        try {
            $dto = new CreateInventoryDTO(
                itemCode: $r->validated('item_code'),
                name: $r->validated('name'),
                unit: $r->validated('unit'),
                organizationId: auth()->user()->organization_id,
                branchId: $r->validated('branch_id'),
                categoryId: $r->validated('category_id'),
                description: $r->validated('description'),
                quantity: $r->validated('quantity'),
                minQuantity: $r->validated('min_quantity'),
                unitPrice: $r->validated('unit_price'),
            );
            return (new InventoryResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateInventoryRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateInventoryDTO(
                itemCode: $r->validated('item_code'),
                name: $r->validated('name'),
                unit: $r->validated('unit'),
                branchId: $r->validated('branch_id'),
                categoryId: $r->validated('category_id'),
                description: $r->validated('description'),
                quantity: $r->validated('quantity'),
                minQuantity: $r->validated('min_quantity'),
                unitPrice: $r->validated('unit_price'),
                isActive: $r->validated('is_active'),
            );
            return (new InventoryResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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

    public function toggleActive(string $id): JsonResponse
    {
        try {
            return (new InventoryResource($this->svc->toggleActive($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}