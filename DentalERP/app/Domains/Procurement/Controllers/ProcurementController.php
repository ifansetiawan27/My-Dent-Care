<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Procurement\DTO\CreateProcurementDTO;
use App\Domains\Procurement\DTO\UpdateProcurementDTO;
use App\Domains\Procurement\Interfaces\ProcurementServiceInterface;
use App\Domains\Procurement\Requests\StoreProcurementRequest;
use App\Domains\Procurement\Requests\UpdateProcurementRequest;
use App\Domains\Procurement\Resources\ProcurementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ProcurementController extends Controller
{
    public function __construct(
        private readonly ProcurementServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return ProcurementResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new ProcurementResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Procurement order not found.'], 404);
        }
    }

    public function store(StoreProcurementRequest $r): JsonResponse
    {
        try {
            $dto = new CreateProcurementDTO(
                orderNumber: $r->validated('order_number'),
                orderDate: $r->validated('order_date'),
                organizationId: auth()->user()->organization_id,
                supplierId: $r->validated('supplier_id'),
                branchId: $r->validated('branch_id'),
                expectedDate: $r->validated('expected_date'),
                totalAmount: $r->validated('total_amount'),
                items: $r->validated('items'),
                notes: $r->validated('notes'),
            );
            return (new ProcurementResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateProcurementRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateProcurementDTO(
                orderNumber: $r->validated('order_number'),
                status: $r->validated('status'),
                supplierId: $r->validated('supplier_id'),
                branchId: $r->validated('branch_id'),
                orderDate: $r->validated('order_date'),
                expectedDate: $r->validated('expected_date'),
                totalAmount: $r->validated('total_amount'),
                items: $r->validated('items'),
                notes: $r->validated('notes'),
            );
            return (new ProcurementResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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