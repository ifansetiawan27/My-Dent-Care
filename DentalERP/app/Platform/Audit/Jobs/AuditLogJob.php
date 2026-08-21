<?php

declare(strict_types=1);

namespace App\Platform\Audit\Jobs;

use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * AuditLogJob
 *
 * Asynchronously persists an audit entry to the audit_logs table.
 * Dispatched by AuditService to avoid blocking domain operations.
 */
class AuditLogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly AuditEntryDTO $entry
    ) {
    }

    public function handle(): void
    {
        AuditLog::create([
            'id' => (string) Str::orderedUuid(),
            'user_id' => $this->entry->userId,
            'organization_id' => $this->entry->organizationId,
            'branch_id' => $this->entry->branchId,
            'module' => $this->entry->module,
            'action' => $this->entry->action->value,
            'auditable_type' => $this->entry->auditableType,
            'auditable_id' => $this->entry->auditableId,
            'old_value' => $this->entry->oldValue,
            'new_value' => $this->entry->newValue,
            'ip_address' => $this->entry->ipAddress,
            'user_agent' => $this->entry->userAgent,
            'device' => $this->entry->device,
            'created_at' => now(),
        ]);
    }
}
