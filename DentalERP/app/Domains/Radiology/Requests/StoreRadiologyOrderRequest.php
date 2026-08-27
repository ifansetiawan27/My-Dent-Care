<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Requests;

use App\Domains\Radiology\Enums\RadiologyPriority;
use App\Domains\Radiology\Enums\RadiologyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRadiologyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'      => 'required|uuid|exists:patients,id',
            'doctor_id'       => 'required|uuid|exists:doctors,id',
            'radiology_type'  => ['required', 'string', Rule::in(RadiologyType::values())],
            'body_part'       => 'nullable|string|max:100',
            'clinical_notes'  => 'nullable|string',
            'priority'        => ['required', 'string', Rule::in(RadiologyPriority::values())],
        ];
    }
}
