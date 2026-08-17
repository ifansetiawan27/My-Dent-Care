<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider'    => ['nullable', 'string', 'max:50'],
            'name'        => ['nullable', 'string', 'max:100'],
            'config'      => ['nullable', 'array'],
            'credentials' => ['nullable', 'array'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }
}