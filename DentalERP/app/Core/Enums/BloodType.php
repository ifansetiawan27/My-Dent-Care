<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * BloodType Enum — shared across Patient and EMR domains.
 */
enum BloodType: string
{
    case A  = 'A';
    case B  = 'B';
    case AB = 'AB';
    case O  = 'O';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::A  => 'A',
            self::B  => 'B',
            self::AB => 'AB',
            self::O  => 'O',
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
