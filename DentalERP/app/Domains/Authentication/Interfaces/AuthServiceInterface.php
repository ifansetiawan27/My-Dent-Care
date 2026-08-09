<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Interfaces;

use App\Domains\Authentication\DTO\LoginDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuthServiceInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function login(LoginDTO $dto): array;

    public function forgotPassword(string $email): void;

    /**
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function resetPassword(string $email, string $token, string $password): void;

    /**
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function changePassword(string $currentPassword, string $newPassword): void;

    /**
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function logout(): void;

    /**
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function logoutAll(): void;

    /**
     * @return array<string, mixed>
     */
    public function getProfile(): array;

    /**
     * @param array<string, mixed> $data
     */
    public function updateProfile(array $data): void;

    /**
     * @param array<string, mixed> $params
     */
    public function getLoginHistory(array $params): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $params
     */
    public function getDevices(array $params): LengthAwarePaginator;

    /**
     * @throws \App\Core\Exceptions\BusinessException
     */
    public function revokeDevice(string $deviceId): void;
}
