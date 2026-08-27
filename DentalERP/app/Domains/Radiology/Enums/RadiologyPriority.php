<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Enums;

enum RadiologyPriority: string
{
    case Routine = 'routine';
    case Urgent  = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Routine => 'Routine',
            self::Urgent  => 'Urgent',
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
