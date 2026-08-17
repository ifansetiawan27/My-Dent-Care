<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Requests;

use App\Domains\Treatment\Enums\TreatmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id'      => 'nullable|uuid|exists:doctors,id',
            'treatment_type' => 'sometimes|string|max:50',
            'status'         => ['sometimes', 'string', Rule::in(TreatmentStatus::values())],
            'cost'           => 'nullable|numeric|min:0',
            'description'    => 'nullable|string',
            'procedure_data' => 'nullable|array',
        ];
    }
}