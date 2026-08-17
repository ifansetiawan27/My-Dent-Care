<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateReportingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => 'sometimes|string|max:50',
            'name'        => 'sometimes|string|max:200',
            'status'      => 'sometimes|string|max:20',
            'report_date' => 'nullable|date',
            'parameters'  => 'nullable|array',
            'data'        => 'nullable|array',
        ];
    }
}