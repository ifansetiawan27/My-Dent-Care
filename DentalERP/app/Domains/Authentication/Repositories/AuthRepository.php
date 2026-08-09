<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Repositories;

use App\Core\Base\BaseRepository;
use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Models\LoginHistory;
use App\Domains\Authentication\Models\PersonalAccessToken;
use App\Domains\Authentication\Models\RefreshToken;
use App\Domains\Authentication\Models\UserDevice;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    protected array $searchable = [];
    protected array $filterable = [];
    protected array $sortable = [];

    public function __construct(
        User $userModel,
        private readonly LoginHistory $loginHistoryModel,
        private readonly UserDevice $userDeviceModel,
        private readonly UserSession $userSessionModel,
        private readonly RefreshToken $refreshTokenModel,
    ) {
        parent::__construct($userModel);
    }

    public function findByUsername(string $username): ?User
    {
        /** @var User|null */
        return $this->model->where('username', $username)->first();
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null */
        return $this->model->where('email', $email)->first();
    }

    public function findByNormalized(string $identifier): ?User
    {
        /** @var User|null */
        return $this->model->where('username', $identifier)->orWhere('email', $identifier)->first();
    }

    public function findDeviceByUuid(string $userId, string $deviceUuid): ?UserDevice
    {
        /** @var UserDevice|null */
        return $this->userDeviceModel->where('user_id', $userId)->where('device_uuid', $deviceUuid)->first();
    }

    public function findSessionById(string $sessionId): ?UserSession
    {
        /** @var UserSession|null */
        return $this->userSessionModel->find($sessionId);
    }

    public function findSessionIdByAccessToken(string $tokenId): ?string
    {
        $token = PersonalAccessToken::find($tokenId);

        return $token?->session_id;
    }

    public function findRefreshTokenByHash(string $tokenHash): ?RefreshToken
    {
        /** @var RefreshToken|null */
        return $this->refreshTokenModel->where('token_hash', $tokenHash)->first();
    }

    public function getActiveUserSessions(string $userId): Collection
    {
        /** @var Collection<int, UserSession> */
        return $this->userSessionModel
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->get();
    }

    public function loginHistoryQuery(string $userId): Builder
    {
        return $this->loginHistoryModel->newQuery()->where('user_id', $userId);
    }

    public function devicesQuery(string $userId): Builder
    {
        return $this->userDeviceModel->newQuery()->where('user_id', $userId);
    }

    public function getActiveUserDeviceSessions(string $deviceId): Collection
    {
        /** @var Collection<int, UserSession> */
        return $this->userSessionModel
            ->where('user_device_id', $deviceId)
            ->whereNull('revoked_at')
            ->get();
    }

    public function createLoginHistory(array $data): LoginHistory
    {
        /** @var LoginHistory */
        return $this->loginHistoryModel->create($data);
    }

    public function createUserDevice(array $data): UserDevice
    {
        /** @var UserDevice */
        return $this->userDeviceModel->create($data);
    }

    public function createUserSession(array $data): UserSession
    {
        /** @var UserSession */
        return $this->userSessionModel->create($data);
    }

    public function createRefreshToken(array $data): RefreshToken
    {
        /** @var RefreshToken */
        return $this->refreshTokenModel->create($data);
    }

    public function updatePassword(string $userId, string $hashedPassword): void
    {
        $this->model->where('id', $userId)->update(['password' => $hashedPassword]);
    }

    public function updateLoginHistoryLogout(string $sessionId): void
    {
        $session = $this->userSessionModel->find($sessionId);
        if ($session === null || $session->login_history_id === null) return;

        $this->loginHistoryModel->where('id', $session->login_history_id)->whereNull('logout_at')->update(['logout_at' => now()]);
    }

    public function revokeRefreshTokenFamily(string $sessionId): void
    {
        $this->refreshTokenModel->where('session_id', $sessionId)->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    public function revokeSession(string $sessionId, string $reason): void
    {
        $this->userSessionModel->where('id', $sessionId)->update(['revoked_at' => now(), 'revoke_reason' => $reason]);
    }

    public function revokeAccessToken(string $sessionId): void
    {
        PersonalAccessToken::where('session_id', $sessionId)->delete();
    }

    public function revokeAllUserSessions(string $userId): void
    {
        $this->userSessionModel->where('user_id', $userId)->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'revoke_reason' => 'password_reset']);
    }

    public function revokeAllUserRefreshTokens(string $userId): void
    {
        $sessionIds = $this->userSessionModel->where('user_id', $userId)->pluck('id');
        $this->refreshTokenModel->whereIn('session_id', $sessionIds)->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    public function revokeAllUserAccessTokens(string $userId): void
    {
        $sessionIds = $this->userSessionModel->where('user_id', $userId)->pluck('id');
        PersonalAccessToken::whereIn('session_id', $sessionIds)->delete();
    }

    public function revokeOtherUserSessions(string $userId, string $excludeSessionId): void
    {
        $this->userSessionModel->where('user_id', $userId)->where('id', '!=', $excludeSessionId)->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'revoke_reason' => 'password_change']);
    }

    public function revokeOtherUserRefreshTokens(string $userId, string $excludeSessionId): void
    {
        $sessionIds = $this->userSessionModel->where('user_id', $userId)->where('id', '!=', $excludeSessionId)->pluck('id');
        $this->refreshTokenModel->whereIn('session_id', $sessionIds)->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    public function revokeOtherUserAccessTokens(string $userId, string $excludeSessionId): void
    {
        $sessionIds = $this->userSessionModel->where('user_id', $userId)->where('id', '!=', $excludeSessionId)->pluck('id');
        PersonalAccessToken::whereIn('session_id', $sessionIds)->delete();
    }
}
