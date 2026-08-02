<?php

declare(strict_types=1);

namespace App\Platform\Audit\DTO;

use App\Platform\Audit\Enums\AuditAction;

/**
 * AuditEntryDTO
 *
 * Immutable value object carrying a single audit record.
 * Domains construct this and pass it to AuditServiceInterface::record().
 * The Audit Platform persists it asynchronously via Queue.
 */
final readonly class AuditEntryDTO
{
    /**
     * @param  AuditAction           $action          The audited action.
     * @param  string                $module          Source module/domain (e.g. 'patient').
     * @param  string|null           $userId          UUID of the acting user.
     * @param  string|null           $organizationId  Tenant organization UUID.
     * @param  string|null           $branchId        Tenant branch UUID.
     * @param  string|null           $auditableType   Affected model class (nullable for login/logout).
     * @param  string|null           $auditableId     Affected record UUID.
     * @param  array<string, mixed>  $oldValue        Data before change (null on create).
     * @param  array<string, mixed>  $newValue        Data after change (null on delete).
     * @param  string|null           $ipAddress       Client IP address.
     * @param  string|null           $userAgent       Client user agent.
     * @param  string|null           $device          Device type (desktop, mobile, api).
     */
    public function __construct(
        public AuditAction $action,
        public string      $module,
        public ?string     $userId         = null,
        public ?string     $organizationId = null,
        public ?string     $branchId       = null,
        public ?string     $auditableType  = null,
        public ?string     $auditableId    = null,
        public array       $oldValue       = [],
        public array       $newValue       = [],
        public ?string     $ipAddress      = null,
        public ?string     $userAgent      = null,
        public ?string     $device         = null,
    ) {}

    /**
     * Serialize to array for persistence.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'action'          => $this->action->value,
            'module'          => $this->module,
            'user_id'         => $this->userId,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'auditable_type'  => $this->auditableType,
            'auditable_id'    => $this->auditableId,
            'old_value'       => $this->oldValue,
            'new_value'       => $this->newValue,
            'ip_address'      => $this->ipAddress,
            'user_agent'      => $this->userAgent,
            'device'          => $this->device,
        ];
    }
}
