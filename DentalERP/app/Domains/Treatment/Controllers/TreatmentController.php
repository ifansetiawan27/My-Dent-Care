<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Treatment\DTO\CreateTreatmentDTO;
use App\Domains\Treatment\DTO\UpdateTreatmentDTO;
use App\Domains\Treatment\Interfaces\TreatmentServiceInterface;
use App\Domains\Treatment\Requests\StoreTreatmentRequest;
use App\Domains\Treatment\Requests\UpdateTreatmentRequest;
use App\Domains\Treatment\Resources\TreatmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class TreatmentController extends Controller
{
    public function __construct(
        private readonly TreatmentServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return TreatmentResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'doctor_id', 'appointment_id', 'status', 'treatment_type', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new TreatmentResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Treatment not found.'], 404);
        }
    }

    public function store(StoreTreatmentRequest $r): JsonResponse
    {
        try {
            $dto = new CreateTreatmentDTO(
                patientId: $r->validated('patient_id'),
                treatmentType: $r->validated('treatment_type'),
                organizationId: auth()->user()->organization_id,
                doctorId: $r->validated('doctor_id'),
                appointmentId: $r->validated('appointment_id'),
                cost: $r->validated('cost'),
                description: $r->validated('description'),
                procedureData: $r->validated('procedure_data'),
            );
            return (new TreatmentResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateTreatmentRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateTreatmentDTO(
                doctorId: $r->validated('doctor_id'),
                treatmentType: $r->validated('treatment_type'),
                status: $r->validated('status'),
                cost: $r->validated('cost'),
                description: $r->validated('description'),
                procedureData: $r->validated('procedure_data'),
            );
            return (new TreatmentResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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