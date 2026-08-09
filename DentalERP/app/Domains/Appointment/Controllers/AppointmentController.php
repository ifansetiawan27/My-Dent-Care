<?php
declare(strict_types=1);
namespace App\Domains\Appointment\Controllers;
use App\Domains\Appointment\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AppointmentController extends Controller {
    public function __construct(private AppointmentService $svc) {}
    public function index(): JsonResponse { return response()->json($this->svc->paginate(['organization_id'=>auth()->user()->organization_id,...request()->only(['branch_id','doctor_id','patient_id','status','date_from','date_to','per_page','page'])])); }
    public function show(string $id): JsonResponse { return response()->json($this->svc->findById($id,auth()->user()->organization_id)); }
    public function store(Request $r): JsonResponse { return response()->json($this->svc->create($r->validate(['organization_id'=>'required|uuid','branch_id'=>'nullable|uuid','patient_id'=>'nullable|uuid|exists:patients,id','doctor_id'=>'nullable|uuid|exists:doctors,id','scheduled_at'=>'required|date','end_at'=>'nullable|date|after:scheduled_at','status'=>'sometimes|string','type'=>'nullable|string','notes'=>'nullable|string'])),201); }
    public function update(string $id, Request $r): JsonResponse { return response()->json($this->svc->update($id,$r->validate(['scheduled_at'=>'sometimes|date','end_at'=>'nullable|date|after:scheduled_at','status'=>'sometimes|string','type'=>'nullable|string','notes'=>'nullable|string']),auth()->user()->organization_id)); }
    public function destroy(string $id): JsonResponse { $this->svc->delete($id,auth()->user()->organization_id); return response()->json(['success'=>true,'message'=>'Deleted.']); }
}
