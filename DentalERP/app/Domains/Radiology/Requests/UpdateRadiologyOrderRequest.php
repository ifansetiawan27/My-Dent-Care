<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Requests;

use App\Domains\Radiology\Enums\RadiologyOrderStatus;
use App\Domains\Radiology\Enums\RadiologyPriority;
use App\Domains\Radiology\Enums\RadiologyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRadiologyOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'     => 'nullable|uuid|exists:patients,id',
            'doctor_id'      => 'nullable|uuid|exists:doctors,id',
            'radiology_type' => ['sometimes', 'string', Rule::in(RadiologyType::values())],
            'body_part'      => 'nullable|string|max:100',
            'clinical_notes' => 'nullable|string',
            'priority'       => ['sometimes', 'string', Rule::in(RadiologyPriority::values())],
            'status'         => ['sometimes', 'string', Rule::in(RadiologyOrderStatus::values())],
        ];
    }
}
