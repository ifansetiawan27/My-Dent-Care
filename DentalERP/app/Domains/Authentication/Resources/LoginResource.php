<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Resources;

use App\Core\Base\BaseResource;
use Illuminate\Http\Request;

class LoginResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tokenPair = $this->resource['token_pair'] ?? null;

        return [
            'token_type'               => $tokenPair->tokenType ?? 'Bearer',
            'access_token'             => $tokenPair->accessToken ?? '',
            'access_token_expires_at'  => $tokenPair->accessTokenExpiresAt ?? '',
            'refresh_token'            => $tokenPair->refreshToken ?? '',
            'refresh_token_expires_at' => $tokenPair->refreshTokenExpiresAt ?? '',
            'device_id'                => $this->resource['device_id'] ?? '',
            'user'                     => $this->when(
                isset($this->resource['user']),
                fn () => new UserSummaryResource($this->resource['user']),
            ),
            'roles'       => $this->resource['roles'] ?? [],
            'permissions' => $this->resource['permissions'] ?? [],
        ];
    }
}
