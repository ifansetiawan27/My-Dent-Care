<?php

declare(strict_types=1);

namespace App\Domains\EMR\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEMRRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id'     => 'required|uuid|exists:organizations,id',
            'patient_id'          => 'required|uuid|exists:patients,id',
            'doctor_id'           => 'nullable|uuid|exists:doctors,id',
            'appointment_id'      => 'nullable|uuid|exists:appointments,id',
            'examination_date'    => 'nullable|date',
            'tooth_number'        => 'nullable|string|max:50',
            'icd_code'            => 'nullable|string|max:20',
            'chief_complaint'     => 'nullable|string',
            'present_illness'     => 'nullable|string',
            'medical_history'     => 'nullable|string',
            'allergies'           => 'nullable|string',
            'vital_signs'         => 'nullable|array',
            'extra_oral_exam'     => 'nullable|string',
            'intra_oral_exam'     => 'nullable|string',
            'radiology_findings'  => 'nullable|string',
            'diagnosis'           => 'nullable|string',
            'secondary_diagnosis' => 'nullable|string',
            'treatment_notes'     => 'nullable|string',
            'treatment_plan'      => 'nullable|string',
            'prescription'        => 'nullable|string',
            'follow_up_plan'      => 'nullable|string',
            'status'              => 'sometimes|string|max:20',
        ];
    }
}
