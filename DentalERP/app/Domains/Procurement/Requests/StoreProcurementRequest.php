<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number'  => 'required|string|max:50',
            'order_date'    => 'required|date',
            'supplier_id'   => 'nullable|uuid|exists:suppliers,id',
            'branch_id'     => 'nullable|uuid|exists:branches,id',
            'expected_date' => 'nullable|date',
            'total_amount'  => 'nullable|numeric|min:0',
            'items'         => 'nullable|array',
            'notes'         => 'nullable|string',
        ];
    }
}