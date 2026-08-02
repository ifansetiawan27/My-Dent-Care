<?php

declare(strict_types=1);

namespace App\Domains\User\DTO;

/**
 * ResetPasswordDTO
 *
 * Immutable value object carrying data for an administrator resetting a user's password.
 * Does NOT require the current password — this is an admin-only operation.
 * Passed directly to UserService::resetPassword().
 */
final readonly class ResetPasswordDTO
{
    /**
     * @param  string $newPassword  The new plaintext password — hashed by the model cast.
     */
    public function __construct(
        public readonly string $newPassword,
    ) {}
}
