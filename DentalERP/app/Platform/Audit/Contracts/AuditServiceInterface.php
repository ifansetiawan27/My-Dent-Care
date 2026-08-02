<?php

declare(strict_types=1);

namespace App\Platform\Audit\Contracts;

use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;

/**
 * AuditServiceInterface
 *
 * The single contract through which every domain records audit activity.
 * Implementations persist audit entries asynchronously (via Queue) and immutably.
 *
 * Platform rule: Domains depend on this interface only — never on a concrete logger,
 * database table, or queue implementation.
 */
interface AuditServiceInterface
{
    /**
     * Record a fully-formed audit entry.
     *
     * @param  AuditEntryDTO $entry
     * @return void
     */
    public function record(AuditEntryDTO $entry): void;

    /**
     * Convenience recorder for a data mutation (create/update/delete/restore).
     *
     * @param  AuditAction          $action
     * @param  string               $module
     * @param  string               $auditableType
     * @param  string               $auditableId
     * @param  array<string, mixed> $oldValue
     * @param  array<string, mixed> $newValue
     * @return void
     */
    public function log(
        AuditAction $action,
        string      $module,
        string      $auditableType,
        string      $auditableId,
        array       $oldValue = [],
        array       $newValue = [],
    ): void;
}
