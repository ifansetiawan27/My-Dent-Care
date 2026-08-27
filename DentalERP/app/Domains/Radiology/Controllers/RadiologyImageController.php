<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Radiology\DTO\CreateRadiologyImageDTO;
use App\Domains\Radiology\Interfaces\RadiologyImageServiceInterface;
use App\Domains\Radiology\Requests\StoreRadiologyImageRequest;
use App\Domains\Radiology\Resources\RadiologyImageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class RadiologyImageController extends Controller
{
    public function __construct(
        private readonly RadiologyImageServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return RadiologyImageResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['radiology_order_id', 'image_type', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new RadiologyImageResource($this->svc->findById($id, auth()->user()->organization_id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Radiology image not found.'], 404);
        }
    }

    public function store(StoreRadiologyImageRequest $r): JsonResponse
    {
        try {
            $dto = new CreateRadiologyImageDTO(
                radiologyOrderId: $r->validated('radiology_order_id'),
                imageType: $r->validated('image_type'),
                filePath: $r->validated('file_path'),
                fileSize: $r->validated('file_size'),
                fileMime: $r->validated('file_mime'),
                thumbnailPath: $r->validated('thumbnail_path'),
                uploadedBy: $r->validated('uploaded_by'),
            );
            return (new RadiologyImageResource($this->svc->create($dto)))->response()->setStatusCode(201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (NotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(string $id, StoreRadiologyImageRequest $r): JsonResponse
    {
        try {
            return (new RadiologyImageResource($this->svc->update(
                $id,
                array_filter([
                    'file_path'       => $r->validated('file_path'),
                    'file_size'       => $r->validated('file_size'),
                    'file_mime'       => $r->validated('file_mime'),
                    'thumbnail_path'  => $r->validated('thumbnail_path'),
                ], fn ($v) => $v !== null),
                auth()->user()->organization_id,
            )))->response();
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
