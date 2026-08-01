<?php

declare(strict_types=1);

namespace App\Domains\Organization\Enums;

enum OrganizationStatus: string
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Suspended = 'suspended';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::Active    => 'Active',
            self::Inactive  => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    /**
     * Check whether the organization is operational.
     */
    public function isOperational(): bool
    {
        return $this === self::Active;
    }

    /**
     * Return all case values as a plain array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
