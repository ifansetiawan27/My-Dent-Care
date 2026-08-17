<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePharmacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'drug_code'    => 'sometimes|string|max:50',
            'name'         => 'sometimes|string|max:200',
            'branch_id'    => 'nullable|uuid|exists:branches,id',
            'category'     => 'nullable|string|max:50',
            'quantity'     => 'nullable|numeric|min:0',
            'unit'         => 'nullable|string|max:20',
            'unit_price'   => 'nullable|numeric|min:0',
            'expiry_date'  => 'nullable|date',
            'batch_number' => 'nullable|string|max:50',
            'is_active'    => 'nullable|boolean',
        ];
    }
}