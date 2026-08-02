<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\User\DTO\ChangePasswordDTO;

/**
 * ChangePasswordRequest
 *
 * Validates incoming data for an authenticated user changing their own password.
 * Requires the current password for identity verification (enforced by UserService).
 * On success, produces a ChangePasswordDTO via toDTO().
 */
class ChangePasswordRequest extends BaseRequest
{
    /**
     * Validation rules for changing own password.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Human-readable attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'Current Password',
            'password'         => 'New Password',
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current Password is required.',

            'password.required'  => 'New Password is required.',
            'password.min'       => 'New Password must be at least 8 characters.',
            'password.confirmed' => 'New Password confirmation does not match.',
        ];
    }

    /**
     * Map validated input to a ChangePasswordDTO.
     * Call this in the Controller after validation passes.
     * Note: Business validation (current password correctness) is enforced in UserService.
     */
    public function toDTO(): ChangePasswordDTO
    {
        $validated = $this->validated();

        return new ChangePasswordDTO(
            currentPassword: $validated['current_password'],
            newPassword:     $validated['password'],
        );
    }
}
