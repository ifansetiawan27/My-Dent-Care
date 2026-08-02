<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * ToothType Enum — used by Odontogram and Treatment domains.
 * Distinguishes between permanent (adult) and deciduous (baby) teeth.
 */
enum ToothType: string
{
    case Permanent  = 'permanent';
    case Deciduous  = 'deciduous';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent (Adult)',
            self::Deciduous => 'Deciduous (Baby)',
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
