<?php

declare(strict_types=1);

namespace App\Domains\Integration\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIntegrationMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integration_config_id' => 'required|uuid|exists:integration_configs,id',
            'local_type' => 'required|string|max:50',
            'local_id' => 'required|string|max:100',
            'external_code' => 'required|string|max:100',
            'external_data' => 'nullable|array',
            'is_synced' => 'sometimes|boolean',
        ];
    }
}
