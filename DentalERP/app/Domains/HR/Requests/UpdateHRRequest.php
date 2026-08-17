<?php

declare(strict_types=1);

namespace App\Domains\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateHRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_type'    => 'sometimes|string|max:50',
            'status'         => 'sometimes|string|max:20',
            'employee_id'    => 'nullable|uuid|exists:employees,id',
            'effective_date' => 'nullable|date',
            'end_date'       => 'nullable|date',
            'data'           => 'nullable|array',
            'notes'          => 'nullable|string',
        ];
    }
}