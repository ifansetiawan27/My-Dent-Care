<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Controllers;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScannerDeviceDTO;
use App\Domains\Scanner\DTO\UpdateScannerDeviceDTO;
use App\Domains\Scanner\Interfaces\ScannerDeviceServiceInterface;
use App\Domains\Scanner\Requests\StoreScannerDeviceRequest;
use App\Domains\Scanner\Requests\UpdateScannerDeviceRequest;
use App\Domains\Scanner\Resources\ScannerDeviceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
final class ScannerDeviceController extends Controller {
    public function __construct(private readonly ScannerDeviceServiceInterface $svc) {}
    public function index(): JsonResponse {
        return ScannerDeviceResource::collection($this->svc->paginate(request()->only(['status', 'manufacturer', 'search', 'per_page', 'page', 'sort_by', 'sort_dir'])))->response();
    }
    public function show(string $id): JsonResponse {
        try { return (new ScannerDeviceResource($this->svc->findById($id)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scanner device not found.'], 404); }
    }
    public function store(StoreScannerDeviceRequest $r): JsonResponse {
        $dto = new CreateScannerDeviceDTO(
            deviceName: $r->validated('device_name'),
            model: $r->validated('model'),
            serialNumber: $r->validated('serial_number'),
            manufacturer: $r->validated('manufacturer'),
            firmwareVersion: $r->validated('firmware_version'),
            status: $r->validated('status'),
            location: $r->validated('location'),
            purchaseDate: $r->validated('purchase_date'),
            warrantyExpiryDate: $r->validated('warranty_expiry_date'),
        );
        return (new ScannerDeviceResource($this->svc->create($dto)))->response()->setStatusCode(201);
    }
    public function update(string $id, UpdateScannerDeviceRequest $r): JsonResponse {
        $dto = new UpdateScannerDeviceDTO(
            deviceName: $r->validated('device_name'),
            model: $r->validated('model'),
            serialNumber: $r->validated('serial_number'),
            manufacturer: $r->validated('manufacturer'),
            firmwareVersion: $r->validated('firmware_version'),
            status: $r->validated('status'),
            location: $r->validated('location'),
            purchaseDate: $r->validated('purchase_date'),
            warrantyExpiryDate: $r->validated('warranty_expiry_date'),
        );
        try { return (new ScannerDeviceResource($this->svc->update($id, $dto)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scanner device not found.'], 404); }
    }
    public function destroy(string $id): JsonResponse {
        $this->svc->delete($id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }
    public function calibrate(string $id): JsonResponse {
        try { return (new ScannerDeviceResource($this->svc->calibrateDevice($id)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scanner device not found.'], 404); }
    }
}
