<?php

declare(strict_types=1);

namespace App\Domains\HR\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\HR\DTO\CreateHRDTO;
use App\Domains\HR\DTO\UpdateHRDTO;
use App\Domains\HR\Interfaces\HRServiceInterface;
use App\Domains\HR\Requests\StoreHRRequest;
use App\Domains\HR\Requests\UpdateHRRequest;
use App\Domains\HR\Resources\HRResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class HRController extends Controller
{
    public function __construct(
        private readonly HRServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return HRResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['record_type', 'status', 'employee_id', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new HRResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'HR record not found.'], 404);
        }
    }

    public function store(StoreHRRequest $r): JsonResponse
    {
        try {
            $dto = new CreateHRDTO(
                recordType: $r->validated('record_type'),
                effectiveDate: $r->validated('effective_date'),
                organizationId: auth()->user()->organization_id,
                employeeId: $r->validated('employee_id'),
                endDate: $r->validated('end_date'),
                data: $r->validated('data'),
                notes: $r->validated('notes'),
            );
            return (new HRResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateHRRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateHRDTO(
                recordType: $r->validated('record_type'),
                status: $r->validated('status'),
                employeeId: $r->validated('employee_id'),
                effectiveDate: $r->validated('effective_date'),
                endDate: $r->validated('end_date'),
                data: $r->validated('data'),
                notes: $r->validated('notes'),
            );
            return (new HRResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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