<?php

declare(strict_types=1);

namespace App\Domains\Finance\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Finance\Interfaces\JournalEntryServiceInterface;
use App\Domains\Finance\Requests\StoreJournalEntryRequest;
use App\Domains\Finance\Requests\UpdateJournalEntryRequest;
use App\Domains\Finance\Resources\JournalEntryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class JournalEntryController extends Controller
{
    public function __construct(
        private readonly JournalEntryServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return JournalEntryResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['status', 'entry_date', 'period_date', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new JournalEntryResource($this->svc->findByIdWithOrganization($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Journal Entry not found.'], 404);
        }
    }

    public function store(StoreJournalEntryRequest $r): JsonResponse
    {
        try {
            $data = [
                ...$r->validated(),
                'organization_id' => auth()->user()->organization_id,
                'status'          => 'draft',
            ];
            return (new JournalEntryResource($this->svc->createForOrganization($data, auth()->user()->organization_id)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateJournalEntryRequest $r): JsonResponse
    {
        try {
            return (new JournalEntryResource($this->svc->updateForOrganization($id, $r->validated(), auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->deleteForOrganization($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function post(string $id): JsonResponse
    {
        try {
            return (new JournalEntryResource($this->svc->postJournal($id, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function cancel(string $id): JsonResponse
    {
        try {
            return (new JournalEntryResource($this->svc->cancelJournal($id, auth()->user()->organization_id)))->response();
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
