<?php

declare(strict_types=1);

namespace App\Domains\Authentication\DTO;

/**
 * LoginDTO
 *
 * Immutable value object carrying validated login credentials and device
 * context from the login endpoint. Password is excluded from toArray()
 * for security — it must be verified and discarded, never serialized.
 */
final readonly class LoginDTO
{
    /**
     * @param string      $identifier     Username or email address.
     * @param string      $password       Plaintext password (excluded from serialization).
     * @param string      $organizationId UUID of the target organization.
     * @param string      $branchId       UUID of the target branch.
     * @param string      $deviceUuid     Client-generated device fingerprint.
     * @param string|null $deviceName     Human-readable device label (optional).
     * @param string      $deviceType     Device type: web, mobile, tablet, api.
     * @param string|null $platform       OS or platform identifier (optional).
     */
    public function __construct(
        public readonly string  $identifier,
        public readonly string  $password,
        public readonly string  $organizationId,
        public readonly string  $branchId,
        public readonly string  $deviceUuid,
        public readonly ?string $deviceName = null,
        public readonly string  $deviceType = 'web',
        public readonly ?string $platform   = null,
    ) {}

    /**
     * Convert DTO to array with snake_case keys.
     * Password is intentionally excluded — never serialized.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'identifier'      => $this->identifier,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'device_uuid'     => $this->deviceUuid,
            'device_name'     => $this->deviceName,
            'device_type'     => $this->deviceType,
            'platform'        => $this->platform,
        ];
    }
}
