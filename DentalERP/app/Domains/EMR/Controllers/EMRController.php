<?php

declare(strict_types=1);

namespace App\Domains\EMR\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\EMR\DTO\CreateEMRDTO;
use App\Domains\EMR\DTO\UpdateEMRDTO;
use App\Domains\EMR\Interfaces\EMRServiceInterface;
use App\Domains\EMR\Requests\StoreEMRRequest;
use App\Domains\EMR\Requests\UpdateEMRRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EMRController extends Controller
{
    public function __construct(
        private readonly EMRServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'doctor_id', 'status', 'per_page', 'page']),
        ]));
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->svc->findById($id, auth()->user()->organization_id));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'EMR not found.'], 404);
        }
    }

    public function store(StoreEMRRequest $r): JsonResponse
    {
        try {
            $dto = new CreateEMRDTO(
                organizationId: $r->validated('organization_id'),
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                appointmentId: $r->validated('appointment_id'),
                examinationDate: $r->validated('examination_date'),
                toothNumber: $r->validated('tooth_number'),
                icdCode: $r->validated('icd_code'),
                chiefComplaint: $r->validated('chief_complaint'),
                presentIllness: $r->validated('present_illness'),
                medicalHistory: $r->validated('medical_history'),
                allergies: $r->validated('allergies'),
                vitalSigns: $r->validated('vital_signs'),
                extraOralExam: $r->validated('extra_oral_exam'),
                intraOralExam: $r->validated('intra_oral_exam'),
                radiologyFindings: $r->validated('radiology_findings'),
                diagnosis: $r->validated('diagnosis'),
                secondaryDiagnosis: $r->validated('secondary_diagnosis'),
                treatmentNotes: $r->validated('treatment_notes'),
                treatmentPlan: $r->validated('treatment_plan'),
                prescription: $r->validated('prescription'),
                followUpPlan: $r->validated('follow_up_plan'),
                status: $r->validated('status'),
            );
            return response()->json($this->svc->create($dto), 201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateEMRRequest $r): JsonResponse
    {
        $dto = new UpdateEMRDTO(
            examinationDate: $r->validated('examination_date'),
            toothNumber: $r->validated('tooth_number'),
            icdCode: $r->validated('icd_code'),
            chiefComplaint: $r->validated('chief_complaint'),
            presentIllness: $r->validated('present_illness'),
            medicalHistory: $r->validated('medical_history'),
            allergies: $r->validated('allergies'),
            vitalSigns: $r->validated('vital_signs'),
            extraOralExam: $r->validated('extra_oral_exam'),
            intraOralExam: $r->validated('intra_oral_exam'),
            radiologyFindings: $r->validated('radiology_findings'),
            diagnosis: $r->validated('diagnosis'),
            secondaryDiagnosis: $r->validated('secondary_diagnosis'),
            treatmentNotes: $r->validated('treatment_notes'),
            treatmentPlan: $r->validated('treatment_plan'),
            prescription: $r->validated('prescription'),
            followUpPlan: $r->validated('follow_up_plan'),
            status: $r->validated('status'),
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