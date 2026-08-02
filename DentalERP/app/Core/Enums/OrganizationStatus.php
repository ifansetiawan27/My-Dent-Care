<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * OrganizationStatus — Canonical Core Enum
 *
 * Used by Organization domain and any domain that needs to check
 * whether an organization is operational.
 *
 * NOTE: App\Domains\Organization\Enums\OrganizationStatus is deprecated.
 * Use this Core enum going forward.
 */
enum OrganizationStatus: string
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
