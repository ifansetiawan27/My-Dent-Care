<?php

declare(strict_types=1);

namespace App\Domains\User\DTO;

use App\Domains\User\Enums\UserGender;

/**
 * UpdateProfileDTO
 *
 * Immutable value object carrying validated data for self-profile update.
 * Used when an authenticated user updates their own profile information.
 * Restricted to safe personal fields only — no account or role changes.
 * Passed directly to UserService::updateProfile().
 */
final readonly class UpdateProfileDTO
{
    /**
     * @param  string|null     $name       New display name (optional).
     * @param  string|null     $phone      New phone number (optional).
     * @param  string|null     $photo      New profile photo path (optional).
     * @param  UserGender|null $gender     New gender (optional).
     * @param  string|null     $birthDate  New date of birth — Y-m-d format (optional).
     */
    public function __construct(
        public readonly ?string     $name      = null,
        public readonly ?string     $phone     = null,
        public readonly ?string     $photo     = null,
        public readonly ?UserGender $gender    = null,
        public readonly ?string     $birthDate = null,
    ) {}

    /**
     * Convert DTO to array, excluding null values.
     * Only changed fields are sent to the repository.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name'       => $this->name,
            'phone'      => $this->phone,
            'photo'      => $this->photo,
            'gender'     => $this->gender?->value,
            'birth_date' => $this->birthDate,
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * Determine whether this DTO has any fields to update.
     */
    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }
}
