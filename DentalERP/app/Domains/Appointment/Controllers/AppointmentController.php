<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Appointment\DTO\CreateAppointmentDTO;
use App\Domains\Appointment\DTO\UpdateAppointmentDTO;
use App\Domains\Appointment\Interfaces\AppointmentServiceInterface;
use App\Domains\Appointment\Requests\StoreAppointmentRequest;
use App\Domains\Appointment\Requests\UpdateAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['branch_id', 'doctor_id', 'patient_id', 'status', 'date_from', 'date_to', 'per_page', 'page']),
        ]));
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->svc->findById($id, auth()->user()->organization_id));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }
    }

    public function store(StoreAppointmentRequest $r): JsonResponse
    {
        try {
            $dto = new CreateAppointmentDTO(
                organizationId: $r->validated('organization_id'),
                branchId: $r->validated('branch_id'),
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                scheduledAt: $r->validated('scheduled_at'),
                endAt: $r->validated('end_at'),
                status: $r->validated('status'),
                type: $r->validated('type'),
                notes: $r->validated('notes'),
            );
            return response()->json($this->svc->create($dto), 201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateAppointmentRequest $r): JsonResponse
    {
        $dto = new UpdateAppointmentDTO(
            scheduledAt: $r->validated('scheduled_at'),
            endAt: $r->validated('end_at'),
            status: $r->validated('status'),
            type: $r->validated('type'),
            notes: $r->validated('notes'),
        );
        return response()->json($this->svc->update($id, $dto, auth()->user()->organization_id));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->delete($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $id): JsonResponse
    {
        return response()->json($this->svc->toggleActive($id, auth()->user()->organization_id));
    }
}