<?php

declare(strict_types=1);

namespace App\Domains\AI\Controllers;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\AI\DTO\CreateAIDTO;
use App\Domains\AI\Interfaces\AIServiceInterface;
use App\Domains\AI\Requests\StoreAIRequest;
use App\Domains\AI\Resources\AIResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class AIController extends Controller
{
    public function __construct(
        private readonly AIServiceInterface $svc,
    ) {}

    public function index(): JsonResponse
    {
        $filters = [
            'organization_id' => auth()->user()->organization_id,
            ...request()->only(['query_type', 'status', 'date_from', 'date_to', 'per_page', 'page']),
        ];

        return response()->json(
            AIResource::collection($this->svc->paginate($filters))->response()->getData(true)
        );
    }

    public function show(string $id): JsonResponse
    {
        try {
            $ai = $this->svc->findById($id, auth()->user()->organization_id);
            return response()->json(new AIResource($ai));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'AI query not found.'], 404);
        }
    }

    public function store(StoreAIRequest $r): JsonResponse
    {
        try {
            $dto = new CreateAIDTO(
                organizationId: auth()->user()->organization_id,
                userId: auth()->id(),
                queryType: $r->validated('query_type'),
                prompt: $r->validated('prompt'),
                model: $r->validated('model'),
            );
            return response()->json(new AIResource($this->svc->create($dto)), 201);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function retry(string $id): JsonResponse
    {
        try {
            $ai = $this->svc->retry($id, auth()->user()->organization_id);
            return response()->json(new AIResource($ai));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'AI query not found.'], 404);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(string $id): JsonResponse
    {
        try {
            $ai = $this->svc->cancel($id, auth()->user()->organization_id);
            return response()->json(new AIResource($ai));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'AI query not found.'], 404);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Execute the AI query by calling the configured LLM API.
     * POST /api/v1/ai-queries/{id}/execute
     */
    public function execute(string $id): JsonResponse
    {
        try {
            $ai = $this->svc->executeQuery($id, auth()->user()->organization_id);
            return response()->json(new AIResource($ai));
        } catch (NotFoundException) {
            return response()->json(['success' => false, 'message' => 'AI query not found.'], 404);
        } catch (BusinessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}