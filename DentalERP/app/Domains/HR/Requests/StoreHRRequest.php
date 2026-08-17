<?php

declare(strict_types=1);

namespace App\Domains\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreHRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_type'    => 'required|string|max:50',
            'effective_date' => 'required|date',
            'employee_id'    => 'nullable|uuid|exists:employees,id',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
            'data'           => 'nullable|array',
            'notes'          => 'nullable|string',
        ];
    }
}