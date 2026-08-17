<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number'  => 'sometimes|string|max:50',
            'status'        => 'sometimes|string|max:20',
            'supplier_id'   => 'nullable|uuid|exists:suppliers,id',
            'branch_id'     => 'nullable|uuid|exists:branches,id',
            'order_date'    => 'nullable|date',
            'expected_date' => 'nullable|date',
            'total_amount'  => 'nullable|numeric|min:0',
            'items'         => 'nullable|array',
            'notes'         => 'nullable|string',
        ];
    }
}