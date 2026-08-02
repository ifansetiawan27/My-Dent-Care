<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Gender Enum — shared across Patient, User (HR profile), and clinical records.
 * Use this enum for any gender field in any domain.
 */
enum Gender: string
{
    case Male   = 'male';
    case Female = 'female';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Male   => 'Male',
            self::Female => 'Female',
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
