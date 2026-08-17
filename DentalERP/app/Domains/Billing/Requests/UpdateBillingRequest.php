<?php

declare(strict_types=1);

namespace App\Domains\Billing\Requests;

use App\Domains\Billing\Enums\InvoiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'   => 'nullable|uuid|exists:patients,id',
            'total_amount' => 'nullable|numeric|min:0',
            'paid_amount'  => 'nullable|numeric|min:0',
            'status'       => ['sometimes', 'string', Rule::in(InvoiceStatus::values())],
            'due_date'     => 'nullable|date',
            'items'        => 'nullable|array',
            'notes'        => 'nullable|string',
        ];
    }
}