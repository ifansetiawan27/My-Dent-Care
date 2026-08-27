<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRadiologyImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'radiology_order_id' => 'required|uuid|exists:radiology_orders,id',
            'image_type'         => 'required|string|max:50',
            'file_path'          => 'required|string',
            'file_size'          => 'nullable|integer|min:0',
            'file_mime'          => 'nullable|string|max:100',
            'thumbnail_path'     => 'nullable|string',
            'uploaded_by'        => 'nullable|uuid',
        ];
    }
}
