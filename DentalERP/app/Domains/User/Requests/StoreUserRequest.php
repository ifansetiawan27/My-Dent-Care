<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\User\DTO\CreateUserDTO;
use App\Domains\User\Enums\UserGender;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Requests\Concerns\HasUserValidationRules;
use Illuminate\Validation\Rule;

/**
 * StoreUserRequest
 *
 * Validates incoming data for creating a new User account.
 * On success, produces a CreateUserDTO via toDTO().
 */
class StoreUserRequest extends BaseRequest
{
    use HasUserValidationRules;

    /**
     * Validation rules for creating a user account.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Multi-tenant scope
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'branch_id'       => ['required', 'uuid', 'exists:branches,id'],

            // Identity
            'employee_code'   => ['required', 'string', 'max:30', 'alpha_dash'],
            'name'            => ['required', 'string', 'max:200'],
            'username'        => ['required', 'string', 'max:100', 'alpha_dash'],
            'email'           => ['required', 'string', 'email:rfc', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:30'],

            // Security
            'password'        => ['required', 'string', 'min:8', 'confirmed'],

            // Profile
            'gender'          => ['nullable', 'string', Rule::in($this->genderValues())],
            'birth_date'      => ['nullable', 'date', 'date_format:Y-m-d', 'before:today'],

            // Status
            'status'          => ['required', 'string', Rule::in($this->statusValues())],
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
            'organization_id' => 'Organization',
            'branch_id'       => 'Branch',
            'employee_code'   => 'Employee Code',
            'name'            => 'Full Name',
            'username'        => 'Username',
            'email'           => 'Email Address',
            'phone'           => 'Phone Number',
            'password'        => 'Password',
            'gender'          => 'Gender',
            'birth_date'      => 'Date of Birth',
            'status'          => 'Status',
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
            // organization_id
            'organization_id.required' => 'Organization is required.',
            'organization_id.uuid'     => 'Organization ID must be a valid UUID.',
            'organization_id.exists'   => 'The selected Organization does not exist.',

            // branch_id
            'branch_id.required' => 'Branch is required.',
            'branch_id.uuid'     => 'Branch ID must be a valid UUID.',
            'branch_id.exists'   => 'The selected Branch does not exist.',

            // employee_code
            'employee_code.required'   => 'Employee Code is required.',
            'employee_code.max'        => 'Employee Code may not exceed 30 characters.',
            'employee_code.alpha_dash' => 'Employee Code may only contain letters, numbers, dashes, and underscores.',

            // name
            'name.required' => 'Full Name is required.',
            'name.max'      => 'Full Name may not exceed 200 characters.',

            // username
            'username.required'   => 'Username is required.',
            'username.max'        => 'Username may not exceed 100 characters.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',

            // email
            'email.required' => 'Email Address is required.',
            'email.email'    => 'Email Address must be a valid email format.',
            'email.max'      => 'Email Address may not exceed 150 characters.',

            // phone
            'phone.max' => 'Phone Number may not exceed 30 characters.',

            // password
            'password.required'  => 'Password is required.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',

            // gender
            'gender.in' => 'Gender must be one of: ' . implode(', ', $this->genderValues()) . '.',

            // birth_date
            'birth_date.date'        => 'Date of Birth must be a valid date.',
            'birth_date.date_format' => 'Date of Birth must be in Y-m-d format (e.g. 1990-01-15).',
            'birth_date.before'      => 'Date of Birth must be a date before today.',

            // status
            'status.required' => 'Status is required.',
            'status.in'       => 'Status must be one of: ' . implode(', ', $this->statusValues()) . '.',
        ];
    }

    /**
     * Map validated input to a CreateUserDTO.
     * Call this in the Controller after validation passes.
     */
    public function toDTO(): CreateUserDTO
    {
        $validated = $this->validated();

        return new CreateUserDTO(
            organizationId: $validated['organization_id'],
            branchId:       $validated['branch_id'],
            employeeCode:   $validated['employee_code'],
            name:           $validated['name'],
            username:       $validated['username'],
            email:          $validated['email'],
            password:       $validated['password'],
            status:         UserStatus::from($validated['status']),
            phone:          $validated['phone']      ?? null,
            gender:         isset($validated['gender'])
                                ? UserGender::from($validated['gender'])
                                : null,
            birthDate:      $validated['birth_date'] ?? null,
        );
    }
}
