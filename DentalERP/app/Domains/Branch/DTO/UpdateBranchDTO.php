<?php

declare(strict_types=1);

namespace App\Domains\Branch\DTO;

use App\Domains\Branch\Enums\BranchStatus;

/**
 * UpdateBranchDTO
 *
 * Immutable value object carrying validated data for updating a Branch.
 * All fields are nullable — only non-null values are applied to the record.
 * Constructed from a validated FormRequest in the Controller layer.
 * Passed directly to BranchService::update().
 */
final readonly class UpdateBranchDTO
{
    /**
     * @param  string|null       $branchCode   New branch code (optional).
     * @param  string|null       $branchName   New branch name (optional).
     * @param  string|null       $branchType   New branch type (optional).
     * @param  string|null       $email        New email (optional).
     * @param  string|null       $phone        New phone (optional).
     * @param  string|null       $address      New street address (optional).
     * @param  string|null       $city         New city (optional).
     * @param  string|null       $province     New province (optional).
     * @param  string|null       $country      New country (optional).
     * @param  string|null       $postalCode   New postal code (optional).
     * @param  string|null       $latitude     New latitude (optional).
     * @param  string|null       $longitude    New longitude (optional).
     * @param  string|null       $timezone     New timezone (optional).
     * @param  BranchStatus|null $status       New status (optional).
     */
    public function __construct(
        public readonly ?string       $branchCode  = null,
        public readonly ?string       $branchName  = null,
        public readonly ?string       $branchType  = null,
        public readonly ?string       $email       = null,
        public readonly ?string       $phone       = null,
        public readonly ?string       $address     = null,
        public readonly ?string       $city        = null,
        public readonly ?string       $province    = null,
        public readonly ?string       $country     = null,
        public readonly ?string       $postalCode  = null,
        public readonly ?string       $latitude    = null,
        public readonly ?string       $longitude   = null,
        public readonly ?string       $timezone    = null,
        public readonly ?BranchStatus $status      = null,
    ) {}

    /**
     * Convert DTO to array, excluding null values.
     * Only changed fields are sent to the repository for update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'branch_code'  => $this->branchCode,
            'branch_name'  => $this->branchName,
            'branch_type'  => $this->branchType,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'address'      => $this->address,
            'city'         => $this->city,
            'province'     => $this->province,
            'country'      => $this->country,
            'postal_code'  => $this->postalCode,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'timezone'     => $this->timezone,
            'status'       => $this->status?->value,
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
