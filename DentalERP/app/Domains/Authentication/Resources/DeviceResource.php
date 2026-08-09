<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

class DeviceResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'device_uuid'      => $this->device_uuid,
            'device_name'      => $this->device_name,
            'platform'         => $this->platform,
            'browser'          => $this->browser,
            'operating_system' => $this->operating_system,
            'last_login_at'    => $this->last_login_at?->toIso8601String(),
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'is_trusted'       => $this->is_trusted,
            'is_active'        => $this->revoked_at === null,
        ];
    }
}
