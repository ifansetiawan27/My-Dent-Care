<?php

declare(strict_types=1);

namespace App\Domains\AI\DTO;

final readonly class UpdateAIDTO
{
    public function __construct(
        public ?string $status = null,
        public ?string $response = null,
        public ?int $tokensUsed = null,
        public ?string $errorMessage = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'status'        => $this->status,
            'response'      => $this->response,
            'tokens_used'   => $this->tokensUsed,
            'error_message' => $this->errorMessage,
        ], fn ($v) => $v !== null);
    }
}