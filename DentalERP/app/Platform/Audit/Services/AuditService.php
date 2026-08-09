<?php

declare(strict_types=1);

namespace App\Platform\Audit\Services;

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Audit\Jobs\AuditLogJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class AuditService implements AuditServiceInterface
{
    public function record(AuditEntryDTO $entry): void
    {
        try {
            AuditLogJob::dispatch($entry);
        } catch (\Throwable $e) {
            Log::error('[AuditService::record] Queue dispatch failed.', [
                'exception' => $e::class,
                'module'    => $entry->module,
                'action'    => $entry->action->value,
            ]);
        }
    }

    public function log(
        AuditAction $action,
        string      $module,
        string      $auditableType,
        string      $auditableId,
        array       $oldValue = [],
        array       $newValue = [],
    ): void {
        $entry = new AuditEntryDTO(
            action:        $action,
            module:        $module,
            userId:        Auth::id()?->toString() ?? null,
            organizationId: $this->resolveOrganizationId(),
            branchId:       $this->resolveBranchId(),
            auditableType: $auditableType,
            auditableId:   $auditableId,
            oldValue:      $oldValue,
            newValue:      $newValue,
        );

        $this->record($entry);
    }

    private function resolveOrganizationId(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'getOrganizationId')) {
            return $user->getOrganizationId();
        }

        return $user->organization_id ?? null;
    }

    private function resolveBranchId(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'getBranchId')) {
            return $user->getBranchId();
        }

        return $user->branch_id ?? null;
    }
}
