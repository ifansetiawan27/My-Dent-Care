<?php

declare(strict_types=1);

namespace App\Domains\Asset\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_code'      => 'required|string|max:50',
            'name'            => 'required|string|max:200',
            'category_id'     => 'nullable|uuid|exists:asset_categories,id',
            'branch_id'       => 'nullable|uuid|exists:branches,id',
            'description'     => 'nullable|string',
            'purchase_date'   => 'nullable|date',
            'purchase_price'  => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'notes'           => 'nullable|string',
        ];
    }
}