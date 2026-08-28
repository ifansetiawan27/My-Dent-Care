<?php

declare(strict_types=1);

namespace App\Domains\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeRequest extends FormRequest
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
            'employee_code'      => 'required|string|max:30',
            'full_name'          => 'required|string|max:200',
            'organization_id'    => 'required|uuid|exists:organizations,id',
            'branch_id'          => 'nullable|uuid|exists:branches,id',
            'employment_status'  => 'required|string|max:20',
            'hire_date'          => 'required|date|before_or_equal:today',
            'resignation_date'   => 'nullable|date|after_or_equal:hire_date',
            'position'           => 'nullable|string|max:100',
            'gender'             => 'nullable|string',
            'religion'           => 'nullable|string',
            'marital_status'     => 'nullable|string',
            'nationality_id'     => 'nullable|uuid|exists:nationalities,id',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'address'            => 'nullable|string',
            'district_id'        => 'nullable|uuid|exists:districts,id',
            'village_id'         => 'nullable|uuid|exists:villages,id',
        ];
    }
}
