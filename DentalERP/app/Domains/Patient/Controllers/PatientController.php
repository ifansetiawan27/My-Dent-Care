<?php

declare(strict_types=1);

namespace App\Domains\Patient\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Patient\DTO\CreatePatientDTO;
use App\Domains\Patient\DTO\UpdatePatientDTO;
use App\Domains\Patient\Interfaces\PatientServiceInterface;
use App\Domains\Patient\Requests\StorePatientRequest;
use App\Domains\Patient\Requests\UpdatePatientRequest;
use App\Domains\Patient\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class PatientController extends Controller
{
    public function __construct(
        private readonly PatientServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return PatientResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['search', 'branch_id', 'patient_type_id', 'is_active', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new PatientResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Patient not found.'], 404);
        }
    }

    public function store(StorePatientRequest $r): JsonResponse
    {
        try {
            $dto = new CreatePatientDTO(
                patientCode: $r->validated('patient_code'), fullName: $r->validated('full_name'),
                organizationId: $r->validated('organization_id'), branchId: $r->validated('branch_id'),
                birthDate: $r->validated('birth_date'), gender: $r->validated('gender'),
                bloodType: $r->validated('blood_type'), religion: $r->validated('religion'),
                maritalStatus: $r->validated('marital_status'), nationalityId: $r->validated('nationality_id'),
                patientTypeId: $r->validated('patient_type_id'), phone: $r->validated('phone'),
                email: $r->validated('email'), address: $r->validated('address'),
                districtId: $r->validated('district_id'), villageId: $r->validated('village_id'),
            );
            return (new PatientResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdatePatientRequest $r): JsonResponse
    {
        $dto = new UpdatePatientDTO(
            patientCode: $r->validated('patient_code'), fullName: $r->validated('full_name'),
            branchId: $r->validated('branch_id'), birthDate: $r->validated('birth_date'),
            gender: $r->validated('gender'), bloodType: $r->validated('blood_type'),
            religion: $r->validated('religion'), maritalStatus: $r->validated('marital_status'),
            nationalityId: $r->validated('nationality_id'), patientTypeId: $r->validated('patient_type_id'),
            phone: $r->validated('phone'), email: $r->validated('email'),
            address: $r->validated('address'), districtId: $r->validated('district_id'),
            villageId: $r->validated('village_id'),
        );
        return (new PatientResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->delete($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $id): JsonResponse
    {
        return (new PatientResource($this->svc->toggleActive($id, auth()->user()->organization_id)))->response();
    }
}