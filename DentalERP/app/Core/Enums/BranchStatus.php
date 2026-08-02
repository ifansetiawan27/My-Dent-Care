<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * BranchStatus — Canonical Core Enum
 *
 * Used by Branch domain and any domain that needs to verify
 * whether a branch is accepting patients or appointments.
 *
 * NOTE: App\Domains\Branch\Enums\BranchStatus is deprecated.
 * Use this Core enum going forward.
 */
enum BranchStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
        };
    }

    public function isOperational(): bool
    {
        return $this === self::Active;
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
