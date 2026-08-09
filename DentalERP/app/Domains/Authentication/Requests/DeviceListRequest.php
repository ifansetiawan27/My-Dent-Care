<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Requests;

use App\Core\Base\BaseRequest;
use Illuminate\Validation\Rule;

class DeviceListRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'page'      => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort'     => ['nullable', 'string', Rule::in(['last_activity_at', 'created_at', 'device_name'])],
            'direction'=> ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'platform' => ['nullable', 'string', 'max:50'],
            'trusted'  => ['nullable', 'boolean'],
            'active'   => ['nullable', 'boolean'],
            'revoked'  => ['nullable', 'boolean'],
        ];
    }
}
