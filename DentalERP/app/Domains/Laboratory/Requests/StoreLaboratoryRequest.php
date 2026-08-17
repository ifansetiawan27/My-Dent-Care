<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLaboratoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'   => 'required|uuid|exists:patients,id',
            'order_number' => 'required|string|max:50|unique:lab_orders,order_number',
            'doctor_id'    => 'nullable|uuid|exists:doctors,id',
            'category_id'  => 'nullable|uuid|exists:laboratory_categories,id',
            'description'  => 'nullable|string',
            'results'      => 'nullable|array',
            'ordered_at'   => 'required|date',
            'notes'        => 'nullable|string',
        ];
    }
}