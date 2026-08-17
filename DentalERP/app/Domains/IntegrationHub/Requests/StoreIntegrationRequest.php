<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider'    => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:100'],
            'config'      => ['nullable', 'array'],
            'credentials' => ['nullable', 'array'],
        ];
    }
}