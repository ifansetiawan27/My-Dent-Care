<?php

declare(strict_types=1);

namespace App\Domains\User\DTO;

use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;

/**
 * CreateUserDTO
 *
 * Immutable value object carrying validated data required to create a User.
 * Constructed from a validated FormRequest in the Controller layer.
 * Passed directly to UserService::create().
 */
final readonly class CreateUserDTO
{
    /**
     * @param  string          $organizationId  UUID of the parent organization.
     * @param  string          $branchId        UUID of the assigned branch.
     * @param  string          $employeeCode    Globally unique employee or staff code.
     * @param  string          $name            Full display name.
     * @param  string          $username        Globally unique login username.
     * @param  string          $email           Globally unique email address.
     * @param  string          $password        Plaintext password — hashed by the model cast.
     * @param  UserStatus      $status          Initial account status.
     * @param  string|null     $phone           Optional phone number.
     * @param  UserGender|null $gender          Optional gender.
     * @param  string|null     $birthDate       Optional date of birth (Y-m-d format).
     */
    public function __construct(
        public readonly string      $organizationId,
        public readonly string      $branchId,
        public readonly string      $employeeCode,
        public readonly string      $name,
        public readonly string      $username,
        public readonly string      $email,
        public readonly string      $password,
        public readonly UserStatus  $status    = UserStatus::Active,
        public readonly ?string     $phone     = null,
        public readonly ?UserGender $gender    = null,
        public readonly ?string     $birthDate = null,
    ) {}

    /**
     * Convert DTO to array for Eloquent mass assignment.
     * Password is passed as plaintext — the model's hashed cast handles encryption.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'employee_code'   => $this->employeeCode,
            'name'            => $this->name,
            'username'        => $this->username,
            'email'           => $this->email,
            'password'        => $this->password,
            'status'          => $this->status->value,
            'phone'           => $this->phone,
            'gender'          => $this->gender?->value,
            'birth_date'      => $this->birthDate,
        ];
    }
}
