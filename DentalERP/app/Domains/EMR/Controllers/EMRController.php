<?php
declare(strict_types=1);
namespace App\Domains\EMR\Controllers;
use App\Domains\EMR\Services\EMRService;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Routing\Controller;

final class EMRController extends Controller {
    public function __construct(private EMRService $svc) {}
    public function index(): JsonResponse { return response()->json($this->svc->paginate(['organization_id'=>auth()->user()->organization_id,...request()->only(['patient_id','doctor_id','status','per_page','page'])])); }
    public function show(string $id): JsonResponse { return response()->json($this->svc->findById($id,auth()->user()->organization_id)); }
    public function store(Request $r): JsonResponse { return response()->json($this->svc->create($r->validate(['organization_id'=>'required|uuid','patient_id'=>'required|uuid|exists:patients,id','doctor_id'=>'nullable|uuid|exists:doctors,id','appointment_id'=>'nullable|uuid|exists:appointments,id','chief_complaint'=>'nullable|string','diagnosis'=>'nullable|string','treatment_notes'=>'nullable|string','vital_signs'=>'nullable|array','status'=>'sometimes|string'])),201); }
    public function update(string $id, Request $r): JsonResponse { return response()->json($this->svc->update($id,$r->validate(['chief_complaint'=>'sometimes|string','diagnosis'=>'sometimes|string','treatment_notes'=>'sometimes|string','vital_signs'=>'nullable|array','status'=>'sometimes|string']),auth()->user()->organization_id)); }
    public function destroy(string $id): JsonResponse { $this->svc->delete($id,auth()->user()->organization_id); return response()->json(['success'=>true,'message'=>'Deleted.']); }
}
