<?php

declare(strict_types=1);

namespace App\Domains\Branch\Enums;

enum BranchStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
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
