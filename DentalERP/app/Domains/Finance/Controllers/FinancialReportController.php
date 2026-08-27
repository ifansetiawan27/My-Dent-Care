<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Interfaces\FinancialReportServiceInterface;
use App\Domains\Finance\Requests\StoreFinancialReportRequest;
use App\Domains\Finance\Requests\UpdateFinancialReportRequest;
use App\Domains\Finance\Resources\FinancialReportResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return FinancialReportResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['report_type', 'status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new FinancialReportResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Financial Report not found.'], 404);
        }
    }

    public function store(StoreFinancialReportRequest $r): JsonResponse
    {
        try {
            $data = [
                ...$r->validated(),
                'organization_id' => auth()->user()->organization_id,
                'status'          => 'pending',
            ];
            return (new FinancialReportResource($this->svc->create($data)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateFinancialReportRequest $r): JsonResponse
    {
        try {
            return (new FinancialReportResource($this->svc->update($id, $r->validated(), auth()->user()->organization_id)))->response();
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

    public function generate(string $id): JsonResponse
    {
        try {
            return (new FinancialReportResource($this->svc->generateReport($id, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
