<?php

declare(strict_types=1);

namespace App\Domains\AI\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\AI\DTO\CreateAIDTO;
use App\Domains\AI\Enums\AIStatus;
use App\Domains\AI\Interfaces\AIRepositoryInterface;
use App\Domains\AI\Interfaces\AIServiceInterface;
use App\Domains\AI\Models\AI;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class AIService implements AIServiceInterface
{
    public function __construct(
        private readonly AIRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): AI
    {
        $ai = $this->repository->findById($id, $organizationId);
        if (! $ai) {
            throw new NotFoundException('AI query not found.');
        }
        return $ai;
    }

    public function create(CreateAIDTO $dto): AI
    {
        return DB::transaction(fn (): AI => $this->repository->create($dto->toArray()));
    }

    /**
     * Execute an AI query by calling the configured LLM API.
     * Supports OpenAI-compatible endpoints (OpenAI, Bailian/DashScope, local LLMs).
     */
    public function executeQuery(string $id, string $organizationId): AI
    {
        $ai = $this->findById($id, $organizationId);

        if ($ai->status !== AIStatus::Pending->value) {
            throw new BusinessException('Only pending queries can be executed.');
        }

        // Update status to processing
        $this->repository->update($ai, ['status' => AIStatus::Processing->value]);

        // Get LLM config from env
        $baseUrl = config('services.ai.base_url', env('AI_API_BASE_URL', 'https://api.openai.com/v1'));
        $apiKey = env('AI_API_KEY');
        $model = config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));

        if (! $apiKey) {
            return $this->markFailed($ai, 'AI_API_KEY not configured');
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a dental clinic assistant. Help dentists and staff with patient information, appointment scheduling, treatment recommendations, and general dental knowledge. Always respond in a professional, concise manner.'],
                        ['role' => 'user', 'content' => $ai->prompt],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                ])
                ->throw()
                ->json();

            $responseText = $response['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $response['usage']['total_tokens'] ?? 0;

            return DB::transaction(fn () => $this->repository->update($ai, [
                'status' => AIStatus::Completed->value,
                'response' => $responseText,
                'tokens_used' => $tokensUsed,
                'completed_at' => now(),
            ]));
        } catch (\Throwable $e) {
            return $this->markFailed($ai, $e->getMessage());
        }
    }

    public function retry(string $id, string $organizationId): AI
    {
        $ai = $this->findById($id, $organizationId);

        if ($ai->status !== AIStatus::Failed->value) {
            throw new BusinessException('Only failed queries can be retried.');
        }

        return DB::transaction(fn (): AI => $this->repository->update($ai, [
            'status'        => AIStatus::Pending->value,
            'error_message' => null,
        ]));
    }

    public function cancel(string $id, string $organizationId): AI
    {
        $ai = $this->findById($id, $organizationId);

        $status = AIStatus::from($ai->status);
        if (! $status->isCancellable()) {
            throw new BusinessException('Only pending or processing queries can be cancelled.');
        }

        return DB::transaction(fn (): AI => $this->repository->update($ai, [
            'status'        => AIStatus::Failed->value,
            'error_message' => 'Cancelled by user',
        ]));
    }

    private function markFailed(AI $ai, string $error): AI
    {
        return DB::transaction(fn () => $this->repository->update($ai, [
            'status' => AIStatus::Failed->value,
            'error_message' => substr($error, 0, 1000),
        ]));
    }
}