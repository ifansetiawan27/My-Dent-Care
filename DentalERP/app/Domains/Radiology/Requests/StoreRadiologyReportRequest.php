<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRadiologyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'radiology_order_id' => 'required|uuid|exists:radiology_orders,id',
            'radiologist_id'     => 'required|uuid|exists:doctors,id',
            'findings'           => 'nullable|string',
            'impression'         => 'nullable|string',
            'diagnosis'          => 'nullable|string',
            'is_final'           => 'nullable|boolean',
        ];
    }
}
