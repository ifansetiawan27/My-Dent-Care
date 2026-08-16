<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Odontogram\DTO\CreateOdontogramDTO;
use App\Domains\Odontogram\DTO\UpdateOdontogramDTO;
use App\Domains\Odontogram\Interfaces\OdontogramServiceInterface;
use App\Domains\Odontogram\Requests\StoreOdontogramRequest;
use App\Domains\Odontogram\Requests\UpdateOdontogramRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class OdontogramController extends Controller
{
    public function __construct(
        private readonly OdontogramServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['patient_id', 'tooth_number', 'per_page', 'page']),
        ]));
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->svc->findById($id, auth()->user()->organization_id));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Odontogram not found.'], 404);
        }
    }

    public function store(StoreOdontogramRequest $r): JsonResponse
    {
        try {
            $dto = new CreateOdontogramDTO(
                organizationId: $r->validated('organization_id'),
                patientId: $r->validated('patient_id'),
                toothNumber: $r->validated('tooth_number'),
                toothType: $r->validated('tooth_type'),
                surface: $r->validated('surface'),
                condition: $r->validated('condition'),
                notes: $r->validated('notes'),
                findings: $r->validated('findings'),
            );
            return response()->json($this->svc->create($dto), 201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function update(string $id, UpdateOdontogramRequest $r): JsonResponse
    {
        $dto = new UpdateOdontogramDTO(
            toothType: $r->validated('tooth_type'),
            surface: $r->validated('surface'),
            condition: $r->validated('condition'),
            notes: $r->validated('notes'),
            findings: $r->validated('findings'),
        );
        return response()->json($this->svc->update($id, $dto, auth()->user()->organization_id));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->svc->delete($id, auth()->user()->organization_id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }

    public function toggleActive(string $id): JsonResponse
    {
        return response()->json($this->svc->toggleActive($id, auth()->user()->organization_id));
    }
}