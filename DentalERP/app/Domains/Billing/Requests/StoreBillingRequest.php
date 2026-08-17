<?php

declare(strict_types=1);

namespace App\Domains\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'   => 'nullable|uuid|exists:patients,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount'  => 'nullable|numeric|min:0',
            'due_date'     => 'nullable|date',
            'items'        => 'nullable|array',
            'notes'        => 'nullable|string',
        ];
    }
}