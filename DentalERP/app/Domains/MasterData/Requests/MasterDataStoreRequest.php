<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class MasterDataStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'code'       => ['required', 'string', 'max:100'],
            'name'       => ['required', 'string', 'max:100'],
            'is_active'  => ['boolean'],
        ];
    }
}
