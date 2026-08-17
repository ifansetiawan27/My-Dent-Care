<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Requests;

use App\Domains\Laboratory\Enums\LabOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLaboratoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id'    => 'nullable|uuid|exists:doctors,id',
            'category_id'  => 'nullable|uuid|exists:laboratory_categories,id',
            'status'       => ['sometimes', 'string', Rule::in(LabOrderStatus::values())],
            'description'  => 'nullable|string',
            'results'      => 'nullable|array',
            'ordered_at'   => 'nullable|date',
            'completed_at' => 'nullable|date',
            'notes'        => 'nullable|string',
        ];
    }
}