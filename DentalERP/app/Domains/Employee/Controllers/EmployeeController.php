<?php
declare(strict_types=1);
namespace App\Domains\Employee\Controllers;
use App\Domains\Employee\Services\EmployeeService;
use App\Domains\Employee\Requests\StoreEmployeeRequest;
use App\Domains\Employee\Requests\UpdateEmployeeRequest;
use App\Domains\Employee\Resources\EmployeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class EmployeeController extends Controller {
    public function __construct(private EmployeeService $service) {}
    public function index(): JsonResponse { return EmployeeResource::collection($this->service->paginate(['organization_id'=>auth()->user()->organization_id,...request()->only(['search','branch_id','is_active','per_page','page','sort_by','sort_dir'])]))->response(); }
    public function show(string $id): JsonResponse { return (new EmployeeResource($this->service->findById($id, auth()->user()->organization_id)))->response(); }
    public function store(StoreEmployeeRequest $r): JsonResponse { return (new EmployeeResource($this->service->create($r->validated())))->response()->setStatusCode(201); }
    public function update(string $id, UpdateEmployeeRequest $r): JsonResponse { return (new EmployeeResource($this->service->update($id, $r->validated(), auth()->user()->organization_id)))->response(); }
    public function destroy(string $id): JsonResponse { $this->service->delete($id, auth()->user()->organization_id); return response()->json(['success'=>true,'message'=>'Deleted.'], 200); }
    public function toggleActive(string $id): JsonResponse { return (new EmployeeResource($this->service->toggleActive($id, auth()->user()->organization_id)))->response(); }
}
