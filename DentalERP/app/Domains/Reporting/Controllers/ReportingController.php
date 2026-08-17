<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Reporting\DTO\CreateReportingDTO;
use App\Domains\Reporting\DTO\UpdateReportingDTO;
use App\Domains\Reporting\Interfaces\ReportingServiceInterface;
use App\Domains\Reporting\Requests\StoreReportingRequest;
use App\Domains\Reporting\Requests\UpdateReportingRequest;
use App\Domains\Reporting\Resources\ReportingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ReportingController extends Controller
{
    public function __construct(
        private readonly ReportingServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return ReportingResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['report_type', 'status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new ReportingResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }
    }

    public function store(StoreReportingRequest $r): JsonResponse
    {
        try {
            $dto = new CreateReportingDTO(
                reportType: $r->validated('report_type'),
                name: $r->validated('name'),
                reportDate: $r->validated('report_date'),
                organizationId: auth()->user()->organization_id,
                parameters: $r->validated('parameters'),
                data: $r->validated('data'),
            );
            return (new ReportingResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateReportingRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateReportingDTO(
                reportType: $r->validated('report_type'),
                name: $r->validated('name'),
                status: $r->validated('status'),
                reportDate: $r->validated('report_date'),
                parameters: $r->validated('parameters'),
                data: $r->validated('data'),
            );
            return (new ReportingResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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