<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Services;

use App\Core\Exceptions\BusinessException;
use App\Domains\Authentication\DTO\TokenPairDTO;
use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Interfaces\TokenServiceInterface;
use App\Domains\Authentication\Models\PersonalAccessToken;
use App\Domains\Authentication\Models\RefreshToken;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TokenService implements TokenServiceInterface
{
    private const SERVICE_NAME = 'TokenService';
    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    public function __construct(
        private readonly AuthRepositoryInterface $repository,
    ) {}

    /**
     * Rotate a refresh token and issue a new access+refresh token pair.
     *
     * Reuse detection: if the token is found with replaced_by_id already set,
     * the entire token family + owning Session + descendant Access Token are
     * revoked atomically and a security error is returned.
     */
    public function refresh(string $plaintextRefreshToken): TokenPairDTO
    {
        $tokenHash = hash('sha256', $plaintextRefreshToken);

        $existing = $this->repository->findRefreshTokenByHash($tokenHash);

        if ($existing === null) {
            throw new BusinessException('Invalid refresh token.');
        }

        if ($existing->expires_at->isPast()) {
            throw new BusinessException('Refresh token has expired.');
        }

        if ($existing->revoked_at !== null && $existing->replaced_by_id !== null) {
            return $this->handleReuse($existing);
        }

        if ($existing->revoked_at !== null) {
            throw new BusinessException('Refresh token has been revoked.');
        }

        return DB::transaction(function () use ($existing): TokenPairDTO {
            $session = $this->repository->findSessionById($existing->session_id);

            if ($session === null || $session->revoked_at !== null) {
                throw new BusinessException('Session has been revoked.');
            }

            $user = $session->user;
            if ($user === null || $user->status !== UserStatus::Active) {
                throw new BusinessException('Account is inactive.');
            }

            $existing->update([
                'revoked_at'    => now(),
                'last_used_at'  => now(),
            ]);

            $newPlaintext = Str::random(64);
            $newToken = $this->repository->createRefreshToken([
                'session_id' => $existing->session_id,
                'token_hash' => hash('sha256', $newPlaintext),
                'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
            ]);

            $existing->update(['replaced_by_id' => $newToken->id]);

            $this->repository->revokeAccessToken($existing->session_id);

            $newAccessToken = $user->createToken(
                'auth-session',
                ['*'],
                now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES),
            );

            PersonalAccessToken::where('id', $newAccessToken->accessToken->id)
                ->update(['session_id' => $existing->session_id]);

            $this->logInfo('refresh', 'Token rotated.', [
                'session_id'     => $existing->session_id,
                'old_token_id'   => $existing->id,
                'new_token_id'   => $newToken->id,
            ]);

            return new TokenPairDTO(
                accessToken:           $newAccessToken->plainTextToken,
                accessTokenExpiresAt:  now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES)->toIso8601String(),
                refreshToken:          $newPlaintext,
                refreshTokenExpiresAt: now()->addDays(self::REFRESH_TOKEN_TTL_DAYS)->toIso8601String(),
            );
        });
    }

    /**
     * Handle reuse of a previously rotated/replaced refresh token.
     *
     * Per ADR-002 + DD-AUTH-007: revoke the entire Refresh Token family,
     * revoke the owning Session, and revoke the descendant Access Token.
     */
    private function handleReuse(RefreshToken $reusedToken): TokenPairDTO
    {
        $sessionId = $reusedToken->session_id;

        DB::transaction(function () use ($sessionId): void {
            $this->repository->revokeRefreshTokenFamily($sessionId);
            $this->repository->revokeSession($sessionId, 'refresh_token_reuse');
            $this->repository->revokeAccessToken($sessionId);
        });

        $this->logWarning('refresh', 'Token reuse detected — family, Session, and Access Token revoked.', [
            'session_id'   => $sessionId,
            'reused_token' => $reusedToken->id,
        ]);

        throw new BusinessException('Refresh token has been reused — all sessions revoked.');
    }

    private function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    private function logWarning(string $action, string $message, array $context = []): void
    {
        Log::warning(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    private function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $e->getMessage(),
            [
                'service'   => self::SERVICE_NAME,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}
