<?php

declare(strict_types=1);

namespace App\Domains\Authentication\DTO;

/**
 * TokenPairDTO
 *
 * Immutable value object carrying the access and refresh token pair
 * returned after a successful authentication. Both tokens include
 * their expiry timestamps for client-side token management.
 */
final readonly class TokenPairDTO
{
    /**
     * @param string $tokenType            Token type prefix (default: Bearer).
     * @param string $accessToken          The access token string.
     * @param string $accessTokenExpiresAt ISO 8601 expiry of the access token.
     * @param string $refreshToken         The refresh token string.
     * @param string $refreshTokenExpiresAt ISO 8601 expiry of the refresh token.
     */
    public function __construct(
        public readonly string $tokenType            = 'Bearer',
        public readonly string $accessToken,
        public readonly string $accessTokenExpiresAt,
        public readonly string $refreshToken,
        public readonly string $refreshTokenExpiresAt,
    ) {}

    /**
     * Convert DTO to array with snake_case keys.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token_type'             => $this->tokenType,
            'access_token'           => $this->accessToken,
            'access_token_expires_at' => $this->accessTokenExpiresAt,
            'refresh_token'          => $this->refreshToken,
            'refresh_token_expires_at' => $this->refreshTokenExpiresAt,
        ];
    }
}
