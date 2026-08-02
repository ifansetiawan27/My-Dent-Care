<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * MaritalStatus Enum — shared across Patient and HR domains.
 */
enum MaritalStatus: string
{
    case Single   = 'single';
    case Married  = 'married';
    case Divorced = 'divorced';
    case Widowed  = 'widowed';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Single   => 'Single',
            self::Married  => 'Married',
            self::Divorced => 'Divorced',
            self::Widowed  => 'Widowed',
        };
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
