<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;

class ForgotPasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:150'],
        ];
    }
}
