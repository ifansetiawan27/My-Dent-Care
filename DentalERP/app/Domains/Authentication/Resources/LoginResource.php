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
        $res       = is_array($this->resource) ? $this->resource : (array) $this->resource;
        $tokenPair = $res['token_pair'] ?? null;

        return [
            'token_type'               => $tokenPair->tokenType ?? 'Bearer',
            'access_token'             => $tokenPair->accessToken ?? '',
            'access_token_expires_at'  => $tokenPair->accessTokenExpiresAt ?? '',
            'refresh_token'            => $tokenPair->refreshToken ?? '',
            'refresh_token_expires_at' => $tokenPair->refreshTokenExpiresAt ?? '',
            'device_id'                => $res['device_id'] ?? '',
            'user'                     => $this->when(
                isset($res['user']),
                fn () => new UserSummaryResource($res['user']),
            ),
            'roles'       => $res['roles'] ?? [],
            'permissions' => $res['permissions'] ?? [],
        ];
    }
}
