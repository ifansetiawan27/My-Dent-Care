<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;

class UpdateProfileRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
        ];
    }
}
