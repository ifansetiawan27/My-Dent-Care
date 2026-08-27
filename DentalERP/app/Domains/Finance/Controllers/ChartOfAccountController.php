<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Interfaces\ChartOfAccountServiceInterface;
use App\Domains\Finance\Requests\StoreChartOfAccountRequest;
use App\Domains\Finance\Requests\UpdateChartOfAccountRequest;
use App\Domains\Finance\Resources\ChartOfAccountResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class ChartOfAccountController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return ChartOfAccountResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['account_type', 'is_active', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new ChartOfAccountResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Chart of Account not found.'], 404);
        }
    }

    public function store(StoreChartOfAccountRequest $r): JsonResponse
    {
        try {
            $data = [
                ...$r->validated(),
                'organization_id' => auth()->user()->organization_id,
            ];
            return (new ChartOfAccountResource($this->svc->create($data)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateChartOfAccountRequest $r): JsonResponse
    {
        try {
            return (new ChartOfAccountResource($this->svc->update($id, $r->validated(), auth()->user()->organization_id)))->response();
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
