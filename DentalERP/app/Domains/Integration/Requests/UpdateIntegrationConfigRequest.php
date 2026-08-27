<?php

declare(strict_types=1);

namespace App\Domains\Integration\Requests;

use App\Domains\Integration\Enums\IntegrationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateIntegrationConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integration_type' => ['sometimes', 'string', Rule::in(IntegrationType::values())],
            'name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
            'endpoint_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'config' => 'nullable|array',
        ];
    }
}
