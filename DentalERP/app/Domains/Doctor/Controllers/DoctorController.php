<?php
declare(strict_types=1);
namespace App\Domains\Doctor\Controllers;
use App\Domains\Doctor\Services\DoctorService;
use App\Domains\Doctor\Requests\StoreDoctorRequest;
use App\Domains\Doctor\Requests\UpdateDoctorRequest;
use App\Domains\Doctor\Resources\DoctorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class DoctorController extends Controller {
    public function __construct(private DoctorService $svc) {}
    public function index(): JsonResponse { return DoctorResource::collection($this->svc->paginate(['organization_id'=>auth()->user()->organization_id,...request()->only(['search','branch_id','specialty_id','is_active','per_page','page','sort_by','sort_dir'])]))->response(); }
    public function show(string $id): JsonResponse { return (new DoctorResource($this->svc->findById($id,auth()->user()->organization_id)))->response(); }
    public function store(StoreDoctorRequest $r): JsonResponse { return (new DoctorResource($this->svc->create($r->validated())))->response()->setStatusCode(201); }
    public function update(string $id, UpdateDoctorRequest $r): JsonResponse { return (new DoctorResource($this->svc->update($id,$r->validated(),auth()->user()->organization_id)))->response(); }
    public function destroy(string $id): JsonResponse { $this->svc->delete($id,auth()->user()->organization_id); return response()->json(['success'=>true,'message'=>'Deleted.'],200); }
    public function toggleActive(string $id): JsonResponse { return (new DoctorResource($this->svc->toggleActive($id,auth()->user()->organization_id)))->response(); }
}
