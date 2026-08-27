<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date'      => 'required|date',
            'period_date'     => 'required|date',
            'description'     => 'nullable|string',
            'lines'           => 'required|array|min:2',
            'lines.*.account_id' => 'required|uuid',
            'lines.*.entry_type' => 'required|in:debit,credit',
            'lines.*.amount'     => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ];
    }
}
