<?php

declare(strict_types=1);

namespace App\Domains\Integration\Controllers;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Integration\Interfaces\IntegrationLogServiceInterface;
use App\Domains\Integration\Resources\IntegrationLogResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class IntegrationLogController extends Controller
{
    public function __construct(
        private readonly IntegrationLogServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        return IntegrationLogResource::collection($this->svc->paginate([
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['integration_config_id', 'status', 'direction', 'per_page', 'page', 'sort_by', 'sort_dir']),
        ]))->response();
    }

    public function show(string $id): JsonResponse
    {
        try {
            return (new IntegrationLogResource($this->svc->findById($id)))->response();
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'Integration log not found.'], 404);
        }
    }
}
