<?php

declare(strict_types=1);

namespace App\Domains\Branch\Requests;

use App\Core\Base\BaseRequest;
use App\Domains\Branch\DTO\CreateBranchDTO;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Requests\Concerns\HasBranchValidationRules;
use Illuminate\Validation\Rule;

/**
 * StoreBranchRequest
 *
 * Validates incoming data for creating a new Branch.
 * On success, produces a CreateBranchDTO via toDTO().
 */
class StoreBranchRequest extends BaseRequest
{
    use HasBranchValidationRules;

    /**
     * Validation rules for creating a branch.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'branch_code'     => ['required', 'string', 'max:30', 'alpha_dash'],
            'branch_name'     => ['required', 'string', 'max:200'],
            'branch_type'     => ['required', 'string', Rule::in($this->branchTypes())],
            'email'           => ['nullable', 'string', 'email:rfc', 'max:150'],
            'phone'           => ['required', 'string', 'max:30'],
            'address'         => ['required', 'string', 'max:500'],
            'city'            => ['required', 'string', 'max:100'],
            'province'        => ['required', 'string', 'max:100'],
            'country'         => ['required', 'string', 'max:100'],
            'postal_code'     => ['required', 'string', 'max:20'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'timezone'        => ['required', 'string', 'timezone'],
            'status'          => ['required', 'string', Rule::in(BranchStatus::values())],
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
            'branch_code'     => 'Branch Code',
            'branch_name'     => 'Branch Name',
            'branch_type'     => 'Branch Type',
            'email'           => 'Email',
            'phone'           => 'Phone',
            'address'         => 'Address',
            'city'            => 'City',
            'province'        => 'Province',
            'country'         => 'Country',
            'postal_code'     => 'Postal Code',
            'latitude'        => 'Latitude',
            'longitude'       => 'Longitude',
            'timezone'        => 'Timezone',
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
            'organization_id.required' => 'Organization is required.',
            'organization_id.uuid'     => 'Organization ID must be a valid UUID.',
            'organization_id.exists'   => 'The selected Organization does not exist.',

            'branch_code.required'   => 'Branch Code is required.',
            'branch_code.max'        => 'Branch Code may not exceed 30 characters.',
            'branch_code.alpha_dash' => 'Branch Code may only contain letters, numbers, dashes, and underscores.',

            'branch_name.required' => 'Branch Name is required.',
            'branch_name.max'      => 'Branch Name may not exceed 200 characters.',

            'branch_type.required' => 'Branch Type is required.',
            'branch_type.in'       => 'Branch Type must be one of: ' . implode(', ', $this->branchTypes()) . '.',

            'email.email' => 'Email must be a valid email address.',
            'email.max'   => 'Email may not exceed 150 characters.',

            'phone.required' => 'Phone number is required.',
            'phone.max'      => 'Phone number may not exceed 30 characters.',

            'address.required' => 'Address is required.',

            'city.required'     => 'City is required.',
            'province.required' => 'Province is required.',
            'country.required'  => 'Country is required.',

            'postal_code.required' => 'Postal Code is required.',
            'postal_code.max'      => 'Postal Code may not exceed 20 characters.',

            'latitude.numeric'  => 'Latitude must be a numeric value.',
            'latitude.between'  => 'Latitude must be between -90 and 90.',
            'longitude.numeric' => 'Longitude must be a numeric value.',
            'longitude.between' => 'Longitude must be between -180 and 180.',

            'timezone.required' => 'Timezone is required.',
            'timezone.timezone' => 'Timezone must be a valid IANA timezone (e.g. Asia/Jakarta).',

            'status.required' => 'Status is required.',
            'status.in'       => 'Status must be one of: ' . implode(', ', BranchStatus::values()) . '.',
        ];
    }

    /**
     * Map validated input to a CreateBranchDTO.
     * Call this in the Controller after validation passes.
     */
    public function toDTO(): CreateBranchDTO
    {
        $validated = $this->validated();

        return new CreateBranchDTO(
            organizationId: $validated['organization_id'],
            branchCode:     $validated['branch_code'],
            branchName:     $validated['branch_name'],
            branchType:     $validated['branch_type'],
            phone:          $validated['phone'],
            address:        $validated['address'],
            city:           $validated['city'],
            province:       $validated['province'],
            country:        $validated['country'],
            postalCode:     $validated['postal_code'],
            timezone:       $validated['timezone'],
            status:         BranchStatus::from($validated['status']),
            email:          $validated['email'] ?? null,
            latitude:       isset($validated['latitude']) ? (string) $validated['latitude'] : null,
            longitude:      isset($validated['longitude']) ? (string) $validated['longitude'] : null,
        );
    }

}
