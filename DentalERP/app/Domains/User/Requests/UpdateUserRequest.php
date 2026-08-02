<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\User\DTO\UpdateUserDTO;
use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Requests\Concerns\HasUserValidationRules;
use Illuminate\Validation\Rule;

/**
 * UpdateUserRequest
 *
 * Validates incoming data for updating an existing User account (admin-level).
 * All fields are optional — only provided fields are validated and updated.
 * On success, produces an UpdateUserDTO via toDTO().
 */
class UpdateUserRequest extends BaseRequest
{
    use HasUserValidationRules;

    /**
     * Validation rules for updating a user account.
     * All fields use `sometimes` — only validate when present in the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Assignment
            'branch_id'     => ['sometimes', 'uuid', 'exists:branches,id'],

            // Identity
            'employee_code' => ['sometimes', 'string', 'max:30', 'alpha_dash'],
            'name'          => ['sometimes', 'string', 'max:200'],
            'username'      => ['sometimes', 'string', 'max:100', 'alpha_dash'],
            'email'         => ['sometimes', 'string', 'email:rfc', 'max:150'],
            'phone'         => ['sometimes', 'nullable', 'string', 'max:30'],

            // Profile
            'gender'        => ['sometimes', 'nullable', 'string', Rule::in($this->genderValues())],
            'birth_date'    => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', 'before:today'],

            // Status
            'status'        => ['sometimes', 'string', Rule::in($this->statusValues())],
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
            'branch_id'     => 'Branch',
            'employee_code' => 'Employee Code',
            'name'          => 'Full Name',
            'username'      => 'Username',
            'email'         => 'Email Address',
            'phone'         => 'Phone Number',
            'gender'        => 'Gender',
            'birth_date'    => 'Date of Birth',
            'status'        => 'Status',
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
            // branch_id
            'branch_id.uuid'   => 'Branch ID must be a valid UUID.',
            'branch_id.exists' => 'The selected Branch does not exist.',

            // employee_code
            'employee_code.max'        => 'Employee Code may not exceed 30 characters.',
            'employee_code.alpha_dash' => 'Employee Code may only contain letters, numbers, dashes, and underscores.',

            // name
            'name.max' => 'Full Name may not exceed 200 characters.',

            // username
            'username.max'        => 'Username may not exceed 100 characters.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',

            // email
            'email.email' => 'Email Address must be a valid email format.',
            'email.max'   => 'Email Address may not exceed 150 characters.',

            // phone
            'phone.max' => 'Phone Number may not exceed 30 characters.',

            // gender
            'gender.in' => 'Gender must be one of: ' . implode(', ', $this->genderValues()) . '.',

            // birth_date
            'birth_date.date'        => 'Date of Birth must be a valid date.',
            'birth_date.date_format' => 'Date of Birth must be in Y-m-d format (e.g. 1990-01-15).',
            'birth_date.before'      => 'Date of Birth must be a date before today.',

            // status
            'status.in' => 'Status must be one of: ' . implode(', ', $this->statusValues()) . '.',
        ];
    }

    /**
     * Map validated input to an UpdateUserDTO.
     * Call this in the Controller after validation passes.
     */
    public function toDTO(): UpdateUserDTO
    {
        $validated = $this->validated();

        return new UpdateUserDTO(
            branchId:     $validated['branch_id']     ?? null,
            employeeCode: $validated['employee_code'] ?? null,
            name:         $validated['name']          ?? null,
            username:     $validated['username']      ?? null,
            email:        $validated['email']         ?? null,
            phone:        array_key_exists('phone', $validated)
                              ? ($validated['phone'] ?? null)
                              : null,
            gender:       isset($validated['gender'])
                              ? UserGender::from($validated['gender'])
                              : null,
            birthDate:    $validated['birth_date'] ?? null,
            status:       isset($validated['status'])
                              ? UserStatus::from($validated['status'])
                              : null,
        );
    }
}
