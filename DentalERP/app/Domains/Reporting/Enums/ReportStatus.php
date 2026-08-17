<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Enums;

enum ReportStatus: string
{
    case Generated = 'generated';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Generated',
            self::Archived  => 'Archived',
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}