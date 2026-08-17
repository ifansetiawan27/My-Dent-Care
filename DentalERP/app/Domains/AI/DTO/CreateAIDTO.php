<?php

declare(strict_types=1);

namespace App\Domains\AI\DTO;

use App\Domains\AI\Enums\AIStatus;

final readonly class CreateAIDTO
{
    public function __construct(
        public string $organizationId,
        public ?string $userId,
        public string $queryType,
        public string $prompt,
        public ?string $model,
    ) {}

    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'user_id'         => $this->userId,
            'query_type'      => $this->queryType,
            'prompt'          => $this->prompt,
            'model'           => $this->model,
            'status'          => AIStatus::Pending->value,
        ];
    }
}