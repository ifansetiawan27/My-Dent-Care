<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

class LoginHistoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'login_at'         => $this->login_at?->toIso8601String(),
            'logout_at'        => $this->logout_at?->toIso8601String(),
            'ip_address'       => $this->ip_address,
            'browser'          => $this->browser,
            'operating_system' => $this->operating_system,
            'device_name'      => $this->device_name,
            'country'          => $this->country,
            'city'             => $this->city,
            'login_status'     => $this->login_status->value,
            'failure_reason'   => $this->failure_reason,
        ];
    }
}
