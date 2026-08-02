<?php

declare(strict_types=1);

namespace App\Domains\User\DTO;

/**
 * ChangePasswordDTO
 *
 * Immutable value object carrying data for a user changing their own password.
 * Requires the current password for identity verification in the Service layer.
 * Passed directly to UserService::changePassword().
 */
final readonly class ChangePasswordDTO
{
    /**
     * @param  string $currentPassword  The user's current plaintext password for verification.
     * @param  string $newPassword      The new plaintext password — hashed by the model cast.
     */
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword,
    ) {}
}
