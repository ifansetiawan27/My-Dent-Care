<?php

declare(strict_types=1);

namespace App\Domains\HR\Enums;

enum HRStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Archived => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}