<?php

declare(strict_types=1);

namespace App\Platform\Audit\Enums;

/**
 * AuditAction
 *
 * Enumerates every auditable action across the entire ERP.
 * Used by the Audit Platform to classify recorded activity.
 */
enum AuditAction: string
{
    case Login       = 'login';
    case Logout      = 'logout';
    case Create      = 'create';
    case Update      = 'update';
    case Delete      = 'delete';
    case Restore     = 'restore';
    case Export      = 'export';
    case Import      = 'import';
    case Print       = 'print';
    case Sync        = 'sync';
    case Integration = 'integration';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Login       => 'Login',
            self::Logout      => 'Logout',
            self::Create      => 'Create',
            self::Update      => 'Update',
            self::Delete      => 'Delete',
            self::Restore     => 'Restore',
            self::Export      => 'Export',
            self::Import      => 'Import',
            self::Print       => 'Print',
            self::Sync        => 'Sync',
            self::Integration => 'Integration',
        };
    }

    /**
     * Whether this action mutates data (has old/new value diff).
     */
    public function isMutation(): bool
    {
        return match ($this) {
            self::Create, self::Update, self::Delete, self::Restore => true,
            default => false,
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
