<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Pharmacy\DTO\CreatePharmacyDTO;
use App\Domains\Pharmacy\DTO\UpdatePharmacyDTO;
use App\Domains\Pharmacy\Interfaces\PharmacyServiceInterface;
use App\Domains\Pharmacy\Requests\StorePharmacyRequest;
use App\Domains\Pharmacy\Requests\UpdatePharmacyRequest;
use App\Domains\Pharmacy\Resources\PharmacyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class PharmacyController extends Controller
{
    public function __construct(
        private readonly PharmacyServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return PharmacyResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['branch_id', 'category', 'is_active', 'expiry_date', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new PharmacyResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Pharmacy item not found.'], 404);
        }
    }

    public function store(StorePharmacyRequest $r): JsonResponse
    {
        try {
            $dto = new CreatePharmacyDTO(
                drugCode: $r->validated('drug_code'),
                name: $r->validated('name'),
                organizationId: auth()->user()->organization_id,
                branchId: $r->validated('branch_id'),
                category: $r->validated('category'),
                quantity: $r->validated('quantity'),
                unit: $r->validated('unit'),
                unitPrice: $r->validated('unit_price'),
                expiryDate: $r->validated('expiry_date'),
                batchNumber: $r->validated('batch_number'),
            );
            return (new PharmacyResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdatePharmacyRequest $r): JsonResponse
    {
        try {
            $dto = new UpdatePharmacyDTO(
                drugCode: $r->validated('drug_code'),
                name: $r->validated('name'),
                branchId: $r->validated('branch_id'),
                category: $r->validated('category'),
                quantity: $r->validated('quantity'),
                unit: $r->validated('unit'),
                unitPrice: $r->validated('unit_price'),
                expiryDate: $r->validated('expiry_date'),
                batchNumber: $r->validated('batch_number'),
                isActive: $r->validated('is_active'),
            );
            return (new PharmacyResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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
            return (new PharmacyResource($this->svc->toggleActive($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}