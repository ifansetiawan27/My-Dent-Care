<?php

declare(strict_types=1);

namespace App\Domains\CRM\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCRMRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_type'   => 'required|string|max:50',
            'patient_id'     => 'nullable|uuid|exists:patients,id',
            'channel'        => 'nullable|string|max:50',
            'subject'        => 'nullable|string|max:200',
            'message'        => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'resolution'     => 'nullable|string',
        ];
    }
}