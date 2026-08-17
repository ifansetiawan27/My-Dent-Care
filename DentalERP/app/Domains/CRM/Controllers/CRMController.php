<?php

declare(strict_types=1);

namespace App\Domains\CRM\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\CRM\DTO\CreateCRMDTO;
use App\Domains\CRM\DTO\UpdateCRMDTO;
use App\Domains\CRM\Interfaces\CRMServiceInterface;
use App\Domains\CRM\Requests\StoreCRMRequest;
use App\Domains\CRM\Requests\UpdateCRMRequest;
use App\Domains\CRM\Resources\CRMResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class CRMController extends Controller
{
    public function __construct(
        private readonly CRMServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return CRMResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['status', 'contact_type', 'search', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new CRMResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'CRM contact not found.'], 404);
        }
    }

    public function store(StoreCRMRequest $r): JsonResponse
    {
        try {
            $dto = new CreateCRMDTO(
                contactType: $r->validated('contact_type'),
                organizationId: auth()->user()->organization_id,
                patientId: $r->validated('patient_id'),
                channel: $r->validated('channel'),
                subject: $r->validated('subject'),
                message: $r->validated('message'),
                followUpDate: $r->validated('follow_up_date'),
                resolution: $r->validated('resolution'),
            );
            return (new CRMResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, UpdateCRMRequest $r): JsonResponse
    {
        try {
            $dto = new UpdateCRMDTO(
                contactType: $r->validated('contact_type'),
                status: $r->validated('status'),
                patientId: $r->validated('patient_id'),
                channel: $r->validated('channel'),
                subject: $r->validated('subject'),
                message: $r->validated('message'),
                followUpDate: $r->validated('follow_up_date'),
                resolution: $r->validated('resolution'),
            );
            return (new CRMResource($this->svc->update($id, $dto, auth()->user()->organization_id)))->response();
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