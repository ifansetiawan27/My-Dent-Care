<?php

declare(strict_types=1);

namespace App\Domains\Odontogram\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOdontogramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Multi-tenant guard: organization_id is always derived from the
     * authenticated user, never trusted from the request payload.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_id' => $this->user()?->organization_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id',
            'patient_id'      => 'required|uuid|exists:patients,id',
            'tooth_number'    => 'required|string|max:5',
            'tooth_type'      => 'nullable|string|max:20',
            'surface'         => 'nullable|string|max:50',
            'condition'       => 'nullable|string|max:50',
            'notes'           => 'nullable|string',
            'findings'        => 'nullable|array',
        ];
    }
}
