<?php

declare(strict_types=1);

namespace App\Domains\Billing\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payment collection request (front-desk cashier workflow).
 */
final class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.numeric'  => 'Jumlah pembayaran harus berupa angka.',
            'amount.min'      => 'Jumlah pembayaran harus lebih besar dari 0.',
        ];
    }
}
