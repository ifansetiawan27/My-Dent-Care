<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Controllers;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScanSessionDTO;
use App\Domains\Scanner\DTO\UpdateScanSessionDTO;
use App\Domains\Scanner\Interfaces\ScanSessionServiceInterface;
use App\Domains\Scanner\Requests\StoreScanSessionRequest;
use App\Domains\Scanner\Requests\UpdateScanSessionRequest;
use App\Domains\Scanner\Resources\ScanSessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
final class ScanSessionController extends Controller {
    public function __construct(private readonly ScanSessionServiceInterface $svc) {}
    public function index(): JsonResponse {
        return ScanSessionResource::collection($this->svc->paginate(request()->only(['patient_id', 'doctor_id', 'device_id', 'status', 'scan_type', 'search', 'per_page', 'page', 'sort_by', 'sort_dir'])))->response();
    }
    public function show(string $id): JsonResponse {
        try { return (new ScanSessionResource($this->svc->findById($id)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scan session not found.'], 404); }
    }
    public function store(StoreScanSessionRequest $r): JsonResponse {
        try {
            $dto = new CreateScanSessionDTO(
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                deviceId: $r->validated('device_id'),
                scanType: $r->validated('scan_type'),
                notes: $r->validated('notes'),
            );
            return (new ScanSessionResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 422); }
    }
    public function update(string $id, UpdateScanSessionRequest $r): JsonResponse {
        try {
            $dto = new UpdateScanSessionDTO(
                patientId: $r->validated('patient_id'),
                doctorId: $r->validated('doctor_id'),
                deviceId: $r->validated('device_id'),
                scanType: $r->validated('scan_type'),
                status: $r->validated('status'),
                notes: $r->validated('notes'),
            );
            return (new ScanSessionResource($this->svc->update($id, $dto)))->response();
        } catch (BusinessException $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 422); }
        catch (NotFoundException $e) { return response()->json(['success' => false, 'message' => 'Scan session not found.'], 404); }
    }
    public function destroy(string $id): JsonResponse {
        try { $this->svc->delete($id); return response()->json(['success' => true, 'message' => 'Deleted.'], 200); }
        catch (BusinessException $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 422); }
    }
    public function complete(string $id): JsonResponse {
        try { return (new ScanSessionResource($this->svc->completeSession($id)))->response(); }
        catch (BusinessException|NotFoundException $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 422); }
    }
    public function fail(string $id): JsonResponse {
        try { return (new ScanSessionResource($this->svc->failSession($id, request()->input('reason', 'Unknown'))))->response(); }
        catch (BusinessException|NotFoundException $e) { return response()->json(['success' => false, 'message' => $e->getMessage()], 422); }
    }
}
