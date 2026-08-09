<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Policies;

use App\Domains\Authentication\Models\UserDevice;
use App\Domains\Authentication\Models\UserSession;
use App\Domains\User\Models\User;

/**
 * AuthPolicy
 *
 * Authorizes Authentication self-service operations per DD-AUTH-003.
 * All profile, login history, device, and session operations are
 * self-service — only the authenticated User's own resources.
 *
 * Super Admin target-user administration is explicitly excluded
 * until accepted authority explicitly authorizes it.
 */
class AuthPolicy
{
    public function viewProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function updateProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function viewLoginHistory(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function viewDevices(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function revokeDevice(User $user, UserDevice $device): bool
    {
        return $user->id === $device->user_id;
    }

    public function revokeSession(User $user, UserSession $session): bool
    {
        return $user->id === $session->user_id;
    }
}
