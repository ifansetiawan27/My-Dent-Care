<?php

declare(strict_types=1);

namespace App\Domains\Billing\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Billing\DTO\CreateBillingDTO;
use App\Domains\Billing\DTO\UpdateBillingDTO;
use App\Domains\Billing\Interfaces\BillingServiceInterface;
use App\Domains\Billing\Requests\StoreBillingRequest;
use App\Domains\Billing\Requests\UpdateBillingRequest;
use App\Domains\Billing\Resources\BillingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class BillingController extends Controller
{
    public function __construct(
        private readonly BillingServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return BillingResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'status', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new BillingResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
    }

    public function store(StoreBillingRequest $r): JsonResponse
    {
        try {
            $dto = new CreateBillingDTO(
                totalAmount: $r->validated('total_amount'),
                organizationId: auth()->user()->organization_id,
                patientId: $r->validated('patient_id'),
                paidAmount: $r->validated('paid_amount'),
                dueDate: $r->validated('due_date'),
                items: $r->validated('items'),
                notes: $r->validated('notes'),
            );
            return (new BillingResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateBillingRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateBillingDTO(
                patientId: $r->validated('patient_id'),
                totalAmount: $r->validated('total_amount'),
                paidAmount: $r->validated('paid_amount'),
                status: $r->validated('status'),
                dueDate: $r->validated('due_date'),
                items: $r->validated('items'),
                notes: $r->validated('notes'),
            );
            return (new BillingResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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