<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Controllers;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Scanner\DTO\CreateScanFileDTO;
use App\Domains\Scanner\Interfaces\ScanFileServiceInterface;
use App\Domains\Scanner\Requests\StoreScanFileRequest;
use App\Domains\Scanner\Resources\ScanFileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
final class ScanFileController extends Controller {
    public function __construct(private readonly ScanFileServiceInterface $svc) {}
    public function index(): JsonResponse {
        return ScanFileResource::collection($this->svc->paginate(request()->only(['scan_session_id', 'file_format', 'processing_status', 'is_primary', 'per_page', 'page', 'sort_by', 'sort_dir'])))->response();
    }
    public function show(string $id): JsonResponse {
        try { return (new ScanFileResource($this->svc->findById($id)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scan file not found.'], 404); }
    }
    public function store(StoreScanFileRequest $r): JsonResponse {
        $dto = new CreateScanFileDTO(
            scanSessionId: $r->validated('scan_session_id'),
            filePath: $r->validated('file_path'),
            fileSize: $r->validated('file_size'),
            fileFormat: $r->validated('file_format'),
            isPrimary: $r->validated('is_primary'),
            processingStatus: $r->validated('processing_status'),
        );
        return (new ScanFileResource($this->svc->create($dto)))->response()->setStatusCode(201);
    }
    public function update(string $id, StoreScanFileRequest $r): JsonResponse {
        $data = $r->validated();
        try { return (new ScanFileResource($this->svc->update($id, $data)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scan file not found.'], 404); }
    }
    public function destroy(string $id): JsonResponse {
        $this->svc->delete($id);
        return response()->json(['success' => true, 'message' => 'Deleted.'], 200);
    }
    public function markProcessed(string $id): JsonResponse {
        try { return (new ScanFileResource($this->svc->markProcessed($id)))->response(); }
        catch (NotFoundException) { return response()->json(['success' => false, 'message' => 'Scan file not found.'], 404); }
    }
}
