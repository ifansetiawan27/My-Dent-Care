<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAppointmentRequest extends FormRequest
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
            'organization_id'  => 'required|uuid|exists:organizations,id',
            'branch_id'        => 'nullable|uuid|exists:branches,id',
            'patient_id'       => 'nullable|uuid|exists:patients,id',
            'doctor_id'        => 'nullable|uuid|exists:doctors,id',
            'scheduled_at'     => 'required|date',
            'end_at'           => 'nullable|date|after:scheduled_at',
            'status'           => 'sometimes|string|max:20',
            'type'             => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'reminder_minutes' => 'nullable|integer|min:0|max:10080',
            'duration_minutes' => 'nullable|integer|min:0|max:1440',
        ];
    }
}
