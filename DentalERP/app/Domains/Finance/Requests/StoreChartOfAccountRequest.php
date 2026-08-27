<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_code'     => 'required|string|max:20',
            'account_name'     => 'required|string|max:255',
            'account_type'     => 'required|in:asset,liability,equity,revenue,expense',
            'account_category' => 'nullable|string',
            'parent_id'        => 'nullable|uuid|exists:chart_of_accounts,id',
            'is_active'        => 'nullable|boolean',
            'description'      => 'nullable|string',
        ];
    }
}
