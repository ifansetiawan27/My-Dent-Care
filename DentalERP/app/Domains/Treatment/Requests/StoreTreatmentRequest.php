<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'      => 'required|uuid|exists:patients,id',
            'treatment_type'  => 'required|string|max:50',
            'doctor_id'       => 'nullable|uuid|exists:doctors,id',
            'appointment_id'  => 'nullable|uuid|exists:appointments,id',
            'cost'            => 'nullable|numeric|min:0',
            'description'     => 'nullable|string',
            'procedure_data'  => 'nullable|array',
        ];
    }
}