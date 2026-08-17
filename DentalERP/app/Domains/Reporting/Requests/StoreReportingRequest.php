<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreReportingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_type' => 'required|string|max:50',
            'name'        => 'required|string|max:200',
            'report_date' => 'required|date',
            'parameters'  => 'nullable|array',
            'data'        => 'nullable|array',
        ];
    }
}