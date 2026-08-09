<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;

class RefreshTokenRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string', 'min:32'],
        ];
    }
}
