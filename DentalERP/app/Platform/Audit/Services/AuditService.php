<?php

declare(strict_types=1);

namespace App\Platform\Audit\Services;

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Audit\Jobs\AuditLogJob;
use Illuminate\Support\Facades\Auth;

/**
 * AuditService
 *
 * Concrete implementation of AuditServiceInterface.
 * Records audit entries asynchronously via Queue to avoid blocking domain operations.
 */
final class AuditService implements AuditServiceInterface
{
    public function record(AuditEntryDTO $entry): void
    {
        dispatch(new AuditLogJob($entry));
    }

    public function log(
        AuditAction $action,
        string      $module,
        string      $auditableType,
        string      $auditableId,
        array       $oldValue = [],
        array       $newValue = [],
    ): void {
        $user = Auth::user();
        $request = request();

        $entry = new AuditEntryDTO(
            action: $action,
            module: $module,
            userId: $user?->id,
            organizationId: $user?->organization_id ?? $request->input('organization_id'),
            branchId: $user?->branch_id ?? $request->input('branch_id'),
            auditableType: $auditableType,
            auditableId: $auditableId,
            oldValue: $oldValue,
            newValue: $newValue,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            device: $this->detectDevice($request->userAgent()),
        );

        $this->record($entry);
    }

    private function detectDevice(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'api';
        }

        if (preg_match('/mobile|android|iphone|ipad|tablet/i', $userAgent)) {
            if (preg_match('/tablet|ipad/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }

        return 'desktop';
    }
}
