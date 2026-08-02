<?php

declare(strict_types=1);

namespace App\Domains\User\DTO;

use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;

/**
 * UpdateUserDTO
 *
 * Immutable value object carrying validated data for admin-level user update.
 * All fields are optional — only provided fields are applied.
 * Used when an administrator modifies another user's account details.
 * Passed directly to UserService::update().
 */
final readonly class UpdateUserDTO
{
    /**
     * @param  string|null     $branchId      New branch assignment (optional).
     * @param  string|null     $employeeCode  New employee code (optional).
     * @param  string|null     $name          New display name (optional).
     * @param  string|null     $username      New username (optional).
     * @param  string|null     $email         New email address (optional).
     * @param  string|null     $phone         New phone number (optional).
     * @param  UserGender|null $gender        New gender (optional).
     * @param  string|null     $birthDate     New date of birth — Y-m-d format (optional).
     * @param  UserStatus|null $status        New account status (optional).
     */
    public function __construct(
        public readonly ?string     $branchId     = null,
        public readonly ?string     $employeeCode = null,
        public readonly ?string     $name         = null,
        public readonly ?string     $username     = null,
        public readonly ?string     $email        = null,
        public readonly ?string     $phone        = null,
        public readonly ?UserGender $gender       = null,
        public readonly ?string     $birthDate    = null,
        public readonly ?UserStatus $status       = null,
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
            'branch_id'     => $this->branchId,
            'employee_code' => $this->employeeCode,
            'name'          => $this->name,
            'username'      => $this->username,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'gender'        => $this->gender?->value,
            'birth_date'    => $this->birthDate,
            'status'        => $this->status?->value,
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
