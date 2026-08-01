<?php

declare(strict_types=1);

namespace App\Domains\Branch\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\Branch\DTO\UpdateBranchDTO;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Requests\Concerns\HasBranchValidationRules;
use Illuminate\Validation\Rule;

/**
 * UpdateBranchRequest
 *
 * Validates incoming data for updating an existing Branch.
 * All fields are optional — only provided fields are validated and updated.
 * On success, produces an UpdateBranchDTO via toDTO().
 */
class UpdateBranchRequest extends BaseRequest
{
    use HasBranchValidationRules;

    /**
     * Validation rules for updating a branch.
     * All fields use `sometimes` — only validate when present in the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_code' => ['sometimes', 'string', 'max:30', 'alpha_dash'],
            'branch_name' => ['sometimes', 'string', 'max:200'],
            'branch_type' => ['sometimes', 'string', Rule::in($this->branchTypes())],
            'email'       => ['sometimes', 'nullable', 'string', 'email:rfc', 'max:150'],
            'phone'       => ['sometimes', 'string', 'max:30'],
            'address'     => ['sometimes', 'string', 'max:500'],
            'city'        => ['sometimes', 'string', 'max:100'],
            'province'    => ['sometimes', 'string', 'max:100'],
            'country'     => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'latitude'    => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'timezone'    => ['sometimes', 'string', 'timezone'],
            'status'      => ['sometimes', 'string', Rule::in(BranchStatus::values())],
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
            'branch_code' => 'Branch Code',
            'branch_name' => 'Branch Name',
            'branch_type' => 'Branch Type',
            'email'       => 'Email',
            'phone'       => 'Phone',
            'address'     => 'Address',
            'city'        => 'City',
            'province'    => 'Province',
            'country'     => 'Country',
            'postal_code' => 'Postal Code',
            'latitude'    => 'Latitude',
            'longitude'   => 'Longitude',
            'timezone'    => 'Timezone',
            'status'      => 'Status',
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
            'branch_code.max'        => 'Branch Code may not exceed 30 characters.',
            'branch_code.alpha_dash' => 'Branch Code may only contain letters, numbers, dashes, and underscores.',

            'branch_name.max' => 'Branch Name may not exceed 200 characters.',

            'branch_type.in' => 'Branch Type must be one of: ' . implode(', ', $this->branchTypes()) . '.',

            'email.email' => 'Email must be a valid email address.',
            'email.max'   => 'Email may not exceed 150 characters.',

            'phone.max' => 'Phone number may not exceed 30 characters.',

            'postal_code.max' => 'Postal Code may not exceed 20 characters.',

            'latitude.numeric'  => 'Latitude must be a numeric value.',
            'latitude.between'  => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a numeric value.',
            'longitude.between' => 'Longitude must be between -180 and 180.',

            'timezone.timezone' => 'Timezone must be a valid IANA timezone (e.g. Asia/Jakarta).',

            'status.in' => 'Status must be one of: ' . implode(', ', BranchStatus::values()) . '.',
        ];
    }

    /**
     * Map validated input to an UpdateBranchDTO.
     * Call this in the Controller after validation passes.
     */
    public function toDTO(): UpdateBranchDTO
    {
        $validated = $this->validated();

        return new UpdateBranchDTO(
            branchCode: $validated['branch_code'] ?? null,
            branchName: $validated['branch_name'] ?? null,
            branchType: $validated['branch_type'] ?? null,
            email:      array_key_exists('email', $validated) ? ($validated['email'] ?? null) : null,
            phone:      $validated['phone']      ?? null,
            address:    $validated['address']    ?? null,
            city:       $validated['city']       ?? null,
            province:   $validated['province']   ?? null,
            country:    $validated['country']    ?? null,
            postalCode: $validated['postal_code'] ?? null,
            latitude:   isset($validated['latitude'])  ? (string) $validated['latitude']  : null,
            longitude:  isset($validated['longitude']) ? (string) $validated['longitude'] : null,
            timezone:   $validated['timezone']   ?? null,
            status:     isset($validated['status']) ? BranchStatus::from($validated['status']) : null,
        );
    }

}
