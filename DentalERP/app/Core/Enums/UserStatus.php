<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * UserStatus — Canonical Core Enum
 *
 * Used by User domain (and Authentication) to control login access.
 * Used by any domain that needs to check whether a user account is active.
 *
 * NOTE: App\Domains\User\Enums\UserStatus is deprecated.
 * Use this Core enum going forward.
 */
enum UserStatus: string
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

    /**
     * Whether a user with this status is allowed to log in.
     */
    public function canLogin(): bool
    {
        return $this === self::Active;
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
