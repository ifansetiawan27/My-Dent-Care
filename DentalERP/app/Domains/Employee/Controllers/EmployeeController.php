<?php

declare(strict_types=1);

namespace App\Domains\Employee\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Employee\DTO\CreateEmployeeDTO;
use App\Domains\Employee\DTO\UpdateEmployeeDTO;
use App\Domains\Employee\Interfaces\EmployeeServiceInterface;
use App\Domains\Employee\Requests\StoreEmployeeRequest;
use App\Domains\Employee\Requests\UpdateEmployeeRequest;
use App\Domains\Employee\Resources\EmployeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeServiceInterface $service,
    ) {}

    public function index(): JsonResponse
    {
        $filters = [
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['search', 'branch_id', 'is_active', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ];

        return EmployeeResource::collection($this->service->paginate($filters))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new EmployeeResource(
                $this->service->findById($id, auth()->user()->organization_id)
            ))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }
    }

    public function store(StoreEmployeeRequest $r): JsonResponse
    {
        try {
            $dto = new CreateEmployeeDTO(
            employeeCode:     $r->validated('employee_code'),
            fullName:         $r->validated('full_name'),
            organizationId:   $r->validated('organization_id'),
            branchId:         $r->validated('branch_id'),
            employmentStatus: $r->validated('employment_status'),
            hireDate:         $r->validated('hire_date'),
            resignationDate:  $r->validated('resignation_date'),
            position:         $r->validated('position'),
            gender:           $r->validated('gender'),
            religion:         $r->validated('religion'),
            maritalStatus:    $r->validated('marital_status'),
            nationalityId:    $r->validated('nationality_id'),
            phone:            $r->validated('phone'),
            email:            $r->validated('email'),
            address:          $r->validated('address'),
            districtId:       $r->validated('district_id'),
            villageId:        $r->validated('village_id'),
        );

        return (new EmployeeResource($this->service->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateEmployeeRequest $r): JsonResponse
    {
        $dto = new UpdateEmployeeDTO(
            employeeCode:     $r->validated('employee_code'),
            fullName:         $r->validated('full_name'),
            branchId:         $r->validated('branch_id'),
            employmentStatus: $r->validated('employment_status'),
            hireDate:         $r->validated('hire_date'),
            resignationDate:  $r->validated('resignation_date'),
            position:         $r->validated('position'),
            gender:           $r->validated('gender'),
            religion:         $r->validated('religion'),
            maritalStatus:    $r->validated('marital_status'),
            nationalityId:    $r->validated('nationality_id'),
            phone:            $r->validated('phone'),
            email:            $r->validated('email'),
            address:          $r->validated('address'),
            districtId:       $r->validated('district_id'),
            villageId:        $r->validated('village_id'),
        );

        return (new EmployeeResource(
            $this->service->update($id, $dto, auth()->user()->organization_id)
        ))->response();
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->delete($id, auth()->user()->organization_id);

        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $id): JsonResponse
    {
        return (new EmployeeResource(
            $this->service->toggleActive($id, auth()->user()->organization_id)
        ))->response();
    }
}