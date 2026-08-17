<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_code'    => 'required|string|max:50',
            'name'         => 'required|string|max:200',
            'unit'         => 'required|string|max:20',
            'branch_id'    => 'nullable|uuid|exists:branches,id',
            'category_id'  => 'nullable|uuid|exists:inventory_categories,id',
            'description'  => 'nullable|string',
            'quantity'     => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|numeric|min:0',
            'unit_price'   => 'nullable|numeric|min:0',
        ];
    }
}