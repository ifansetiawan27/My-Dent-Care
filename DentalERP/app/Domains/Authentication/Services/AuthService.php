<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Services;

use App\Core\Exceptions\BusinessException;
use App\Domains\Authentication\DTO\LoginDTO;
use App\Domains\Authentication\DTO\TokenPairDTO;
use App\Domains\Authentication\Enums\DeviceType;
use App\Domains\Authentication\Enums\LoginStatus;
use App\Domains\Authentication\Interfaces\AuthRepositoryInterface;
use App\Domains\Authentication\Interfaces\AuthServiceInterface;
use App\Domains\Authentication\Interfaces\LockoutServiceInterface;
use App\Domains\Authentication\Models\PersonalAccessToken;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Organization\Enums\OrganizationStatus;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Models\User;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\FileStorage\Enums\StorageFolder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class AuthService implements AuthServiceInterface
{
    private const SERVICE_NAME = 'AuthService';

    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    public function __construct(
        private readonly AuthRepositoryInterface $repository,
        private readonly LockoutServiceInterface $lockoutService,
        private readonly FileStorageServiceInterface $fileStorage,
    ) {}

    public function login(LoginDTO $dto): array
    {
        $identifier = $dto->identifier;
        $ipAddress  = request()->ip() ?? '127.0.0.1';

        $user = $this->repository->findByNormalized($identifier);

        if ($this->lockoutService->isLocked($identifier, $ipAddress)) {
            if ($user === null || ! $user->hasRole('super_admin')) {
                $this->logWarning('login', 'Account locked.', ['identifier' => $identifier]);
                throw new BusinessException('Account temporarily locked. Please try again later.');
            }
        }

        if ($user === null || ! Hash::check($dto->password, $user->password)) {
            if ($user === null || ! $user->hasRole('super_admin')) {
                $this->lockoutService->recordFailure($identifier, $ipAddress);
            }

            $this->logWarning('login', 'Invalid credentials.', ['identifier' => $identifier]);

            throw new BusinessException('Invalid credentials.');
        }

        if (Hash::needsRehash($user->password)) {
            $user->update(['password' => Hash::make($dto->password)]);
        }

        $this->lockoutService->clear($identifier, $ipAddress);

        $this->validateTenantEligibility($user, $dto);

        try {
            return DB::transaction(function () use ($user, $dto): array {
                $device  = $this->resolveDevice($user, $dto);
                $session = $this->createSession($user, $device->id);
                $token   = $this->issueAccessToken($user, $session->id);
                $this->recordLoginHistory($user, $device, true);

                $user->update(['last_login_at' => now()]);

                $refreshPlaintext = Str::random(64);
                $this->repository->createRefreshToken([
                    'session_id' => $session->id,
                    'token_hash' => hash('sha256', $refreshPlaintext),
                    'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
                ]);

                $this->logInfo('login', 'Login successful.', [
                    'user_id'    => $user->id,
                    'session_id' => $session->id,
                    'device_id'  => $device->id,
                ]);

                $tokenPair = new TokenPairDTO(
                    accessToken:           $token->plainTextToken,
                    accessTokenExpiresAt:  now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES)->toIso8601String(),
                    refreshToken:          $refreshPlaintext,
                    refreshTokenExpiresAt: now()->addDays(self::REFRESH_TOKEN_TTL_DAYS)->toIso8601String(),
                );

                return [
                    'token_pair' => $tokenPair,
                    'device_id'  => $device->id,
                    'user'       => $user->fresh() ?? $user,
                    'roles'      => $user->getRoleNames()->toArray(),
                    'permissions'=> $user->getAllPermissions()->pluck('name')->toArray(),
                ];
            });
        } catch (Throwable $e) {
            $this->logError('login', $e, ['user_id' => $user->id]);
            throw $e;
        }
    }

    public function forgotPassword(string $email): void
    {
        $normalized = mb_strtolower(trim($email));

        $user = $this->repository->findByEmail($normalized);

        if ($user === null) {
            $this->logInfo('forgotPassword', 'No user found for email — returning generic response.', ['email' => $normalized]);

            return;
        }

        Password::sendResetLink(['email' => $user->email]);

        $this->logInfo('forgotPassword', 'Password reset link sent.', ['user_id' => $user->id]);
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $normalized = mb_strtolower(trim($email));

        $user = $this->repository->findByEmail($normalized);

        if ($user === null) {
            throw new BusinessException('Invalid password reset request.');
        }

        $status = Password::reset(
            ['email' => $user->email, 'token' => $token, 'password' => $password],
            function (User $u, string $hashedPassword) use ($user): void {
                DB::transaction(function () use ($user, $hashedPassword): void {
                    $this->repository->updatePassword($user->id, $hashedPassword);

                    $this->repository->revokeAllUserSessions($user->id);
                    $this->repository->revokeAllUserRefreshTokens($user->id);
                    $this->repository->revokeAllUserAccessTokens($user->id);

                    $this->logInfo('resetPassword', 'Password reset — all sessions revoked.', [
                        'user_id' => $user->id,
                    ]);
                });
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new BusinessException('Invalid or expired password reset token.');
        }
    }

    public function changePassword(string $currentPassword, string $newPassword): void
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        if (! Hash::check($currentPassword, $user->password)) {
            throw new BusinessException('Current password is incorrect.');
        }

        $currentToken = request()->user()->currentAccessToken();
        $sessionId = $currentToken?->session_id;

        if ($sessionId === null) {
            throw new BusinessException('Current session could not be resolved.');
        }

        DB::transaction(function () use ($user, $newPassword, $sessionId): void {
            $this->repository->updatePassword($user->id, Hash::make($newPassword));

            $this->repository->revokeOtherUserSessions($user->id, $sessionId);
            $this->repository->revokeOtherUserRefreshTokens($user->id, $sessionId);
            $this->repository->revokeOtherUserAccessTokens($user->id, $sessionId);

            $this->logInfo('changePassword', 'Password changed — other sessions revoked, current preserved.', [
                'user_id'    => $user->id,
                'session_id' => $sessionId,
            ]);
        });
    }

    public function logout(): void
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $currentToken = $user->currentAccessToken();
        $sessionId = $currentToken?->session_id;

        if ($sessionId === null) {
            throw new BusinessException('Current session could not be resolved.');
        }

        $session = $this->repository->findSessionById($sessionId);

        if ($session === null || $session->revoked_at !== null) {
            $currentToken->delete();

            return;
        }

        DB::transaction(function () use ($sessionId): void {
            $this->repository->updateLoginHistoryLogout($sessionId);
            $this->repository->revokeRefreshTokenFamily($sessionId);
            $this->repository->revokeSession($sessionId, 'logout');
            $this->repository->revokeAccessToken($sessionId);

            $this->logInfo('logout', 'Session revoked.', ['session_id' => $sessionId]);
        });
    }

    public function logoutAll(): void
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $sessions = $this->repository->getActiveUserSessions($user->id);

        DB::transaction(function () use ($sessions): void {
            foreach ($sessions as $session) {
                $this->repository->updateLoginHistoryLogout($session->id);
                $this->repository->revokeRefreshTokenFamily($session->id);
                $this->repository->revokeSession($session->id, 'logout_all');
                $this->repository->revokeAccessToken($session->id);
            }

            $this->logInfo('logoutAll', 'All sessions revoked.', [
                'user_id'        => request()->user()?->id,
                'session_count'  => count($sessions),
            ]);
        });
    }

    public function getProfile(): array
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        return [
            'user'         => $user,
            'photo_url'    => $user->photo !== null
                ? $this->fileStorage->temporaryUrl($user->photo)
                : null,
            'organization' => $user->organization,
            'branch'       => $user->branch,
            'roles'        => $user->getRoleNames()->toArray(),
            'permissions'  => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    public function updateProfile(array $data): void
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $allowed = [];

        if (array_key_exists('name', $data)) {
            $allowed['name'] = $data['name'];
        }

        if (array_key_exists('phone', $data)) {
            $allowed['phone'] = $data['phone'];
        }

        if (array_key_exists('photo', $data) && $data['photo'] !== null) {
            $photo = $data['photo'];

            $allowed['photo'] = $photo instanceof UploadedFile
                ? $this->fileStorage->store(
                    file:          $photo,
                    folder:        StorageFolder::Organization,
                    organizationId: $user->organization_id,
                    branchId:      $user->branch_id,
                )->path
                : $photo;
        }

        if (! empty($allowed)) {
            $user->update($allowed);
        }

        $this->logInfo('updateProfile', 'Profile updated.', ['user_id' => $user->id]);
    }

    public function getLoginHistory(array $params): LengthAwarePaginator
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $perPage     = (int) ($params['per_page'] ?? 15);
        $perPage     = min($perPage, 100);
        $loginStatus = $params['login_status'] ?? null;
        $from        = $params['from'] ?? null;
        $to          = $params['to'] ?? null;

        $query = $this->repository->loginHistoryQuery($user->id);

        if ($loginStatus !== null) {
            $query->where('login_status', $loginStatus);
        }

        if ($from !== null) {
            $query->where('login_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('login_at', '<=', $to);
        }

        return $query->orderBy('login_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getDevices(array $params): LengthAwarePaginator
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $perPage   = (int) ($params['per_page'] ?? 20);
        $perPage   = min($perPage, 100);
        $sort      = $params['sort'] ?? 'last_activity_at';
        $direction = $params['direction'] ?? 'desc';
        $platform  = $params['platform'] ?? null;
        $trusted   = $params['trusted'] ?? null;
        $active    = $params['active'] ?? null;
        $revoked   = $params['revoked'] ?? null;

        $allowedSorts = ['last_activity_at', 'created_at', 'device_name'];

        if (! in_array($sort, $allowedSorts, true)) {
            throw new BusinessException('Invalid sort field.');
        }

        if (! in_array(mb_strtolower($direction), ['asc', 'desc'], true)) {
            throw new BusinessException('Invalid sort direction.');
        }

        $query = $this->repository->devicesQuery($user->id);

        if ($platform !== null) {
            $query->where('platform', $platform);
        }

        if ($trusted !== null) {
            $query->where('is_trusted', filter_var($trusted, FILTER_VALIDATE_BOOLEAN));
        }

        if ($active !== null && filter_var($active, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNull('revoked_at');
        }

        if ($revoked !== null && filter_var($revoked, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereNotNull('revoked_at');
        }

        return $query->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage);
    }

    public function revokeDevice(string $deviceId): void
    {
        $user = request()->user();

        if ($user === null) {
            throw new BusinessException('Unauthorized.');
        }

        $device = $this->repository->findDeviceByUuid($user->id, $deviceId);

        if ($device === null) {
            throw new BusinessException('Device not found.');
        }

        if ($device->revoked_at !== null) {
            return;
        }

        $currentToken = $user->currentAccessToken();
        $currentSessionId = $currentToken?->session_id;

        if ($currentSessionId !== null) {
            $currentSession = $this->repository->findSessionById($currentSessionId);

            if ($currentSession !== null && $currentSession->user_device_id === $device->id) {
                throw new BusinessException('Cannot revoke the current device. Please use the logout flow.');
            }
        }

        DB::transaction(function () use ($device): void {
            $device->update(['revoked_at' => now()]);

            $sessions = $this->repository->getActiveUserDeviceSessions($device->id);

            foreach ($sessions as $session) {
                $this->repository->updateLoginHistoryLogout($session->id);
                $this->repository->revokeRefreshTokenFamily($session->id);
                $this->repository->revokeSession($session->id, 'device_revoked');
                $this->repository->revokeAccessToken($session->id);
            }

            $this->logInfo('revokeDevice', 'Device and its sessions revoked.', [
                'device_id'    => $device->id,
                'user_id'      => $user->id,
                'session_count'=> count($sessions),
            ]);
        });
    }

    private function validateTenantEligibility(User $user, LoginDTO $dto): void
    {
        if ($user->status !== UserStatus::Active) {
            throw new BusinessException('Account is inactive.');
        }

        $organization = $user->organization;
        if ($organization === null || $organization->status !== OrganizationStatus::Active) {
            throw new BusinessException('Organization is inactive or invalid.');
        }

        if ($organization->id !== $dto->organizationId) {
            throw new BusinessException('Invalid tenant context.');
        }

        $branch = $user->branch;
        if ($branch === null || $branch->status !== BranchStatus::Active) {
            throw new BusinessException('Branch is inactive or invalid.');
        }

        if ($branch->id !== $dto->branchId) {
            throw new BusinessException('Invalid branch context.');
        }
    }

    private function resolveDevice(User $user, LoginDTO $dto): object
    {
        $device = $this->repository->findDeviceByUuid($user->id, $dto->deviceUuid);

        if ($device !== null && $device->revoked_at !== null) {
            throw new BusinessException('Device has been revoked.');
        }

        $deviceType = DeviceType::tryFrom($dto->deviceType) ?? DeviceType::Web;

        if ($device !== null) {
            $device->update([
                'device_name'       => $dto->deviceName ?? $device->device_name,
                'platform'          => $dto->platform ?? $device->platform,
                'device_type'       => $deviceType->value,
                'last_login_at'     => now(),
                'last_activity_at'  => now(),
                'user_agent'        => request()->userAgent(),
                'ip_address'        => request()->ip(),
            ]);

            return $device;
        }

        return $this->repository->createUserDevice([
            'user_id'          => $user->id,
            'organization_id'  => $dto->organizationId,
            'branch_id'        => $dto->branchId,
            'device_uuid'      => $dto->deviceUuid,
            'device_name'      => $dto->deviceName,
            'device_type'      => $deviceType->value,
            'platform'         => $dto->platform,
            'user_agent'       => request()->userAgent(),
            'ip_address'       => request()->ip(),
            'last_login_at'    => now(),
            'last_activity_at' => now(),
        ]);
    }

    private function createSession(User $user, string $deviceId): object
    {
        return $this->repository->createUserSession([
            'user_id'          => $user->id,
            'organization_id'  => $user->organization_id,
            'branch_id'        => $user->branch_id,
            'user_device_id'   => $deviceId,
            'started_at'       => now(),
            'expires_at'       => now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES),
        ]);
    }

    private function issueAccessToken(User $user, string $sessionId): object
    {
        $abilities = ['*'];

        $token = $user->createToken('auth-session', $abilities, now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES));

        PersonalAccessToken::where('id', $token->accessToken->id)
            ->update(['session_id' => $sessionId]);

        $token->accessToken->session_id = $sessionId;

        return $token;
    }

    private function recordLoginHistory(User $user, object $device, bool $success): void
    {
        $this->repository->createLoginHistory([
            'user_id'          => $user->id,
            'organization_id'  => $user->organization_id,
            'branch_id'        => $user->branch_id,
            'device_id'        => $device->id,
            'identifier'       => mb_strtolower(trim($user->username ?? $user->email ?? '')),
            'login_status'     => LoginStatus::Success->value,
            'failure_reason'   => null,
            'ip_address'       => request()->ip(),
            'browser'          => null,
            'operating_system' => null,
            'device_name'      => $device->device_name ?? null,
            'login_at'         => now(),
            'logout_at'        => null,
        ]);
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
