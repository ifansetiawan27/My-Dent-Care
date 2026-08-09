<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Interfaces;

use App\Domains\Authentication\Models\LoginHistory;
use App\Domains\Authentication\Models\RefreshToken;
use App\Domains\Authentication\Models\UserDevice;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface AuthRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function findByNormalized(string $identifier): ?User;

    public function findDeviceByUuid(string $userId, string $deviceUuid): ?UserDevice;

    public function findSessionById(string $sessionId): ?UserSession;

    public function findSessionIdByAccessToken(string $tokenId): ?string;

    public function findRefreshTokenByHash(string $tokenHash): ?RefreshToken;

    public function getActiveUserSessions(string $userId): Collection;

    public function getActiveUserDeviceSessions(string $deviceId): Collection;

    public function loginHistoryQuery(string $userId): Builder;

    public function devicesQuery(string $userId): Builder;

    public function createLoginHistory(array $data): LoginHistory;

    public function createUserDevice(array $data): UserDevice;

    public function createUserSession(array $data): UserSession;

    public function createRefreshToken(array $data): RefreshToken;

    public function updatePassword(string $userId, string $hashedPassword): void;

    public function updateLoginHistoryLogout(string $sessionId): void;

    public function revokeRefreshTokenFamily(string $sessionId): void;

    public function revokeSession(string $sessionId, string $reason): void;

    public function revokeAccessToken(string $sessionId): void;

    public function revokeAllUserSessions(string $userId): void;

    public function revokeAllUserRefreshTokens(string $userId): void;

    public function revokeAllUserAccessTokens(string $userId): void;

    public function revokeOtherUserSessions(string $userId, string $excludeSessionId): void;

    public function revokeOtherUserRefreshTokens(string $userId, string $excludeSessionId): void;

    public function revokeOtherUserAccessTokens(string $userId, string $excludeSessionId): void;
}
