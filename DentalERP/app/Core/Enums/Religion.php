<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Religion Enum — shared across Patient and HR domains.
 * Values follow Indonesian Ministry of Home Affairs official categories.
 */
enum Religion: string
{
    case Islam     = 'islam';
    case Christian = 'christian';
    case Catholic  = 'catholic';
    case Hindu     = 'hindu';
    case Buddha    = 'buddha';
    case Konghucu  = 'konghucu';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Islam     => 'Islam',
            self::Christian => 'Kristen',
            self::Catholic  => 'Katolik',
            self::Hindu     => 'Hindu',
            self::Buddha    => 'Buddha',
            self::Konghucu  => 'Konghucu',
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
