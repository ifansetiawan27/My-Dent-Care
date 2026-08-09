<?php

declare(strict_types=1);

namespace App\Platform\Audit\Jobs;

use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class AuditLogJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly AuditEntryDTO $entry,
    ) {}

    public function handle(): void
    {
        AuditLog::create([
            'id'              => AuditLog::newUuid(),
            'user_id'         => $this->entry->userId,
            'organization_id' => $this->entry->organizationId,
            'branch_id'       => $this->entry->branchId,
            'module'          => $this->entry->module,
            'action'          => $this->entry->action->value,
            'auditable_type'  => $this->entry->auditableType,
            'auditable_id'    => $this->entry->auditableId,
            'old_value'       => $this->entry->oldValue,
            'new_value'       => $this->entry->newValue,
            'ip_address'      => $this->entry->ipAddress,
            'user_agent'      => $this->entry->userAgent,
            'device'          => $this->entry->device,
            'created_at'      => now(),
        ]);
    }
}
