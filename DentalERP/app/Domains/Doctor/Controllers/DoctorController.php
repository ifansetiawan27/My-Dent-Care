<?php

declare(strict_types=1);

namespace App\Domains\Doctor\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Doctor\DTO\CreateDoctorDTO;
use App\Domains\Doctor\DTO\UpdateDoctorDTO;
use App\Domains\Doctor\Interfaces\DoctorServiceInterface;
use App\Domains\Doctor\Requests\StoreDoctorRequest;
use App\Domains\Doctor\Requests\UpdateDoctorRequest;
use App\Domains\Doctor\Resources\DoctorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class DoctorController extends Controller
{
    public function __construct(
        private readonly DoctorServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return DoctorResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['search', 'branch_id', 'specialty_id', 'is_active', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new DoctorResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Doctor not found.'], 404);
        }
    }

    public function store(StoreDoctorRequest $r): JsonResponse
    {
        try {
            $dto = new CreateDoctorDTO(
                doctorCode: $r->validated('doctor_code'), fullName: $r->validated('full_name'),
                organizationId: $r->validated('organization_id'), branchId: $r->validated('branch_id'),
                specialtyId: $r->validated('specialty_id'), licenseNumber: $r->validated('license_number'),
                consultationFee: $r->validated('consultation_fee'), gender: $r->validated('gender'),
                religion: $r->validated('religion'), maritalStatus: $r->validated('marital_status'),
                nationalityId: $r->validated('nationality_id'), phone: $r->validated('phone'),
                email: $r->validated('email'), address: $r->validated('address'),
                districtId: $r->validated('district_id'), villageId: $r->validated('village_id'),
                hireDate: $r->validated('hire_date'), resignationDate: $r->validated('resignation_date'),
            );
            return (new DoctorResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateDoctorRequest $r): JsonResponse
    {
        $dto = new UpdateDoctorDTO(
            doctorCode: $r->validated('doctor_code'), fullName: $r->validated('full_name'),
            branchId: $r->validated('branch_id'), specialtyId: $r->validated('specialty_id'),
            licenseNumber: $r->validated('license_number'), consultationFee: $r->validated('consultation_fee'),
            gender: $r->validated('gender'), religion: $r->validated('religion'),
            maritalStatus: $r->validated('marital_status'), nationalityId: $r->validated('nationality_id'),
            phone: $r->validated('phone'), email: $r->validated('email'),
            address: $r->validated('address'), districtId: $r->validated('district_id'),
            villageId: $r->validated('village_id'), hireDate: $r->validated('hire_date'),
            resignationDate: $r->validated('resignation_date'),
        );
        return (new DoctorResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->delete($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $id): JsonResponse
    {
        return (new DoctorResource($this->svc->toggleActive($id, auth()->user()->organization_id)))->response();
    }
}