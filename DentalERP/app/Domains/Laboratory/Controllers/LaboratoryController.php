<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Laboratory\DTO\CreateLaboratoryDTO;
use App\Domains\Laboratory\DTO\UpdateLaboratoryDTO;
use App\Domains\Laboratory\Interfaces\LaboratoryServiceInterface;
use App\Domains\Laboratory\Requests\StoreLaboratoryRequest;
use App\Domains\Laboratory\Requests\UpdateLaboratoryRequest;
use App\Domains\Laboratory\Resources\LaboratoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class LaboratoryController extends Controller
{
    public function __construct(
        private readonly LaboratoryServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return LaboratoryResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'doctor_id', 'category_id', 'status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new LaboratoryResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Lab order not found.'], 404);
        }
    }

    public function store(StoreLaboratoryRequest $r): JsonResponse
    {
        try {
            $dto = new CreateLaboratoryDTO(
                patientId: $r->validated('patient_id'),
                orderNumber: $r->validated('order_number'),
                organizationId: auth()->user()->organization_id,
                orderedAt: $r->validated('ordered_at'),
                doctorId: $r->validated('doctor_id'),
                categoryId: $r->validated('category_id'),
                description: $r->validated('description'),
                results: $r->validated('results'),
                notes: $r->validated('notes'),
            );
            return (new LaboratoryResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateLaboratoryRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateLaboratoryDTO(
                doctorId: $r->validated('doctor_id'),
                categoryId: $r->validated('category_id'),
                status: $r->validated('status'),
                description: $r->validated('description'),
                results: $r->validated('results'),
                orderedAt: $r->validated('ordered_at'),
                completedAt: $r->validated('completed_at'),
                notes: $r->validated('notes'),
            );
            return (new LaboratoryResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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