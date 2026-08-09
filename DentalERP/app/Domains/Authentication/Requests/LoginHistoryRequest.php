<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\Authentication\Enums\LoginStatus;
use Illuminate\Validation\Rule;

class LoginHistoryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page'         => ['nullable', 'integer', 'min:1'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
            'login_status' => ['nullable', 'string', Rule::in(LoginStatus::values())],
            'from'         => ['nullable', 'string', 'date'],
            'to'           => ['nullable', 'string', 'date'],
        ];
    }
}
