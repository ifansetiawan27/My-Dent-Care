<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'sometimes|string|max:200',
            'user_id'    => 'nullable|uuid|exists:users,id',
            'config'     => 'nullable|array',
            'widgets'    => 'nullable|array',
            'is_default' => 'nullable|boolean',
        ];
    }
}