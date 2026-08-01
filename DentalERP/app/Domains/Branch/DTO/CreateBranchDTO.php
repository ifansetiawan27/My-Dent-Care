<?php

declare(strict_types=1);

namespace App\Domains\Branch\DTO;

use App\Domains\Branch\Enums\BranchStatus;

/**
 * CreateBranchDTO
 *
 * Immutable value object carrying validated data required to create a Branch.
 * Constructed from a validated FormRequest in the Controller layer.
 * Passed directly to BranchService::create().
 */
final readonly class CreateBranchDTO
{
    /**
     * @param  string       $organizationId  UUID of the parent organization.
     * @param  string       $branchCode      Unique code within the organization.
     * @param  string       $branchName      Display name of the branch.
     * @param  string       $branchType      Type: clinic, mobile, hospital.
     * @param  string       $phone           Primary contact phone number.
     * @param  string       $address         Street address.
     * @param  string       $city            City.
     * @param  string       $province        Province or state.
     * @param  string       $country         Country name.
     * @param  string       $postalCode      Postal or ZIP code.
     * @param  string       $timezone        IANA timezone identifier.
     * @param  BranchStatus $status          Initial status.
     * @param  string|null  $email           Contact email (optional).
     * @param  string|null  $latitude        Geographic latitude (optional).
     * @param  string|null  $longitude       Geographic longitude (optional).
     */
    public function __construct(
        public readonly string       $organizationId,
        public readonly string       $branchCode,
        public readonly string       $branchName,
        public readonly string       $branchType,
        public readonly string       $phone,
        public readonly string       $address,
        public readonly string       $city,
        public readonly string       $province,
        public readonly string       $country,
        public readonly string       $postalCode,
        public readonly string       $timezone,
        public readonly BranchStatus $status     = BranchStatus::Active,
        public readonly ?string      $email      = null,
        public readonly ?string      $latitude   = null,
        public readonly ?string      $longitude  = null,
    ) {}

    /**
     * Convert DTO to array for Eloquent mass assignment.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'organization_id' => $this->organizationId,
            'branch_code'     => $this->branchCode,
            'branch_name'     => $this->branchName,
            'branch_type'     => $this->branchType,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'address'         => $this->address,
            'city'            => $this->city,
            'province'        => $this->province,
            'country'         => $this->country,
            'postal_code'     => $this->postalCode,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'timezone'        => $this->timezone,
            'status'          => $this->status->value,
        ];
    }
}
