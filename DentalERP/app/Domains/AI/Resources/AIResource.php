<?php

declare(strict_types=1);

namespace App\Domains\AI\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

class AIResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $isList = ! $request->routeIs('ai-queries.show');

        return [
            'id'              => $this->id,
            'organization_id' => $this->organization_id,
            'user_id'         => $this->user_id,
            'query_type'      => $this->query_type,
            'prompt'          => $this->truncatePrompt($isList ? 200 : 500),
            'response'        => $isList ? null : $this->truncateText($this->response, 500),
            'model'           => $this->model,
            'tokens_used'     => $this->tokens_used,
            'status'          => $this->status,
            'status_label'    => $this->status ? \App\Domains\AI\Enums\AIStatus::from($this->status)->label() : null,
            'error_message'   => $this->error_message,
            ...$this->auditFields(),
        ];
    }

    private function truncatePrompt(int $maxLength): string
    {
        return $this->truncateText($this->prompt, $maxLength);
    }

    private function truncateText(?string $text, int $maxLength): ?string
    {
        if ($text === null) {
            return null;
        }

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength) . '...';
    }
}