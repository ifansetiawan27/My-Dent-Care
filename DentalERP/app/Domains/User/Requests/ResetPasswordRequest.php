<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\User\DTO\ResetPasswordDTO;

/**
 * ResetPasswordRequest
 *
 * Validates incoming data for an administrator resetting a user's password.
 * Does NOT require the current password — this is an admin-only operation.
 * On success, produces a ResetPasswordDTO via toDTO().
 */
class ResetPasswordRequest extends BaseRequest
{
    /**
     * Validation rules for admin password reset.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'password' => 'New Password',
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
            'password.required'  => 'New Password is required.',
            'password.min'       => 'New Password must be at least 8 characters.',
            'password.confirmed' => 'New Password confirmation does not match.',
        ];
    }

    /**
     * Map validated input to a ResetPasswordDTO.
     * Call this in the Controller after validation passes.
     */
    public function toDTO(): ResetPasswordDTO
    {
        $validated = $this->validated();

        return new ResetPasswordDTO(
            newPassword: $validated['password'],
        );
    }
}
