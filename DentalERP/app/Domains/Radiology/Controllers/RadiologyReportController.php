<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyReportDTO;
use App\Domains\Radiology\DTO\UpdateRadiologyReportDTO;
use App\Domains\Radiology\Interfaces\RadiologyReportServiceInterface;
use App\Domains\Radiology\Requests\StoreRadiologyReportRequest;
use App\Domains\Radiology\Requests\UpdateRadiologyReportRequest;
use App\Domains\Radiology\Resources\RadiologyReportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class RadiologyReportController extends Controller
{
    public function __construct(
        private readonly RadiologyReportServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return RadiologyReportResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['radiology_order_id', 'is_final', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new RadiologyReportResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Radiology report not found.'], 404);
        }
    }

    public function store(StoreRadiologyReportRequest $r): JsonResponse
    {
        try {
            $dto = new CreateRadiologyReportDTO(
                radiologyOrderId: $r->validated('radiology_order_id'),
                radiologistId: $r->validated('radiologist_id'),
                organizationId: auth()->user()->organization_id,
                findings: $r->validated('findings'),
                impression: $r->validated('impression'),
                diagnosis: $r->validated('diagnosis'),
                isFinal: $r->validated('is_final'),
            );
            return (new RadiologyReportResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateRadiologyReportRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateRadiologyReportDTO(
                findings: $r->validated('findings'),
                impression: $r->validated('impression'),
                diagnosis: $r->validated('diagnosis'),
                isFinal: $r->validated('is_final'),
            );
            return (new RadiologyReportResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->svc->delete($id, auth()->user()->organization_id);
            return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function finalize(string $id): JsonResponse
    {
        try {
            return (new RadiologyReportResource($this->svc->finalizeReport($id, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
