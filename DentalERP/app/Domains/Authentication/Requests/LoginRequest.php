<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\Authentication\Enums\DeviceType;
use Illuminate\Validation\Rule;

class LoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'identifier'      => ['required', 'string', 'max:150'],
            'password'        => ['required', 'string', 'min:8'],
            'organization_id' => ['required', 'string', 'uuid'],
            'branch_id'       => ['required', 'string', 'uuid'],
            'device_uuid'     => ['required', 'string', 'min:1', 'max:100'],
            'device_name'     => ['nullable', 'string', 'max:150'],
            'device_type'     => ['required', 'string', Rule::in(DeviceType::values())],
            'platform'        => ['nullable', 'string', 'max:50'],
        ];
    }
}
