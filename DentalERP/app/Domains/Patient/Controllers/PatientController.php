<?php
declare(strict_types=1);
namespace App\Domains\Patient\Controllers;
use App\Domains\Patient\Services\PatientService;
use App\Domains\Patient\Requests\StorePatientRequest;
use App\Domains\Patient\Requests\UpdatePatientRequest;
use App\Domains\Patient\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class PatientController extends Controller {
    public function __construct(private PatientService $svc) {}
    public function index(): JsonResponse { return PatientResource::collection($this->svc->paginate(['organization_id'=>auth()->user()->organization_id,...request()->only(['search','branch_id','patient_type_id','is_active','per_page','page','sort_by','sort_dir'])]))->response(); }
    public function show(string $id): JsonResponse { return (new PatientResource($this->svc->findById($id,auth()->user()->organization_id)))->response(); }
    public function store(StorePatientRequest $r): JsonResponse { return (new PatientResource($this->svc->create($r->validated())))->response()->setStatusCode(201); }
    public function update(string $id, UpdatePatientRequest $r): JsonResponse { return (new PatientResource($this->svc->update($id,$r->validated(),auth()->user()->organization_id)))->response(); }
    public function destroy(string $id): JsonResponse { $this->svc->delete($id,auth()->user()->organization_id); return response()->json(['success'=>true,'message'=>'Deleted.'],200); }
    public function toggleActive(string $id): JsonResponse { return (new PatientResource($this->svc->toggleActive($id,auth()->user()->organization_id)))->response(); }
}
