<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRadiologyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'findings'   => 'nullable|string',
            'impression' => 'nullable|string',
            'diagnosis'  => 'nullable|string',
            'is_final'   => 'nullable|boolean',
        ];
    }
}
