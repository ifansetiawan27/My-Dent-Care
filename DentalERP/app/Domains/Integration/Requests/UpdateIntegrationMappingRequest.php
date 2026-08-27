<?php

declare(strict_types=1);

namespace App\Domains\Integration\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'local_type' => 'sometimes|string|max:50',
            'local_id' => 'sometimes|string|max:100',
            'external_code' => 'sometimes|string|max:100',
            'external_data' => 'nullable|array',
            'is_synced' => 'sometimes|boolean',
        ];
    }
}
