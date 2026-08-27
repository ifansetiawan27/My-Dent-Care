<?php

declare(strict_types=1);

namespace App\Domains\Finance\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date'      => 'sometimes|date',
            'period_date'     => 'sometimes|date',
            'description'     => 'nullable|string',
            'lines'           => 'sometimes|array|min:2',
            'lines.*.account_id' => 'required_with:lines|uuid',
            'lines.*.entry_type' => ['required_with:lines', 'string', Rule::in(['debit', 'credit'])],
            'lines.*.amount'     => 'required_with:lines|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $journalEntry = $this->route('id');
            if ($journalEntry) {
                $model = \App\Domains\Finance\Models\JournalEntry::find($journalEntry);
                if ($model && $model->status !== 'draft') {
                    $validator->errors()->add('status', 'Can only update journal entries in draft status.');
                }
            }
        });
    }
}
