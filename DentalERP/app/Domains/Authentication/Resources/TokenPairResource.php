<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

class TokenPairResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token_type'               => $this->resource->tokenType ?? 'Bearer',
            'access_token'             => $this->resource->accessToken ?? '',
            'access_token_expires_at'  => $this->resource->accessTokenExpiresAt ?? '',
            'refresh_token'            => $this->resource->refreshToken ?? '',
            'refresh_token_expires_at' => $this->resource->refreshTokenExpiresAt ?? '',
        ];
    }
}
