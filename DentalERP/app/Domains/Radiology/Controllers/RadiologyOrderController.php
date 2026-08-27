<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyOrderDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyOrderDTO;
use App\Domains\Radiology\Interfaces\RadiologyOrderServiceInterface;
use App\Domains\Radiology\Requests\StoreRadiologyOrderRequest;
use App\Domains\Radiology\Requests\UpdateRadiologyOrderRequest;
use App\Domains\Radiology\Resources\RadiologyOrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class RadiologyOrderController extends Controller
{
    public function __construct(
        private readonly RadiologyOrderServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return RadiologyOrderResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'status', 'radiology_type', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new RadiologyOrderResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Radiology order not found.'], 404);
        }
    }

    public function store(StoreRadiologyOrderRequest $r): JsonResponse
    {
        try {
            $dto = new CreateRadiologyOrderDTO(
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                radiologyType: $r->validated('radiology_type'),
                priority: $r->validated('priority'),
                organizationId: auth()->user()->organization_id,
                bodyPart: $r->validated('body_part'),
                clinicalNotes: $r->validated('clinical_notes'),
            );
            return (new RadiologyOrderResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateRadiologyOrderRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateRadiologyOrderDTO(
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                radiologyType: $r->validated('radiology_type'),
                bodyPart: $r->validated('body_part'),
                clinicalNotes: $r->validated('clinical_notes'),
                priority: $r->validated('priority'),
                status: $r->validated('status'),
            );
            return (new RadiologyOrderResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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

    public function complete(string $id): JsonResponse
    {
        try {
            return (new RadiologyOrderResource($this->svc->completeOrder($id, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
