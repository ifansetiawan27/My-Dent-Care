<?php

declare(strict_types=1);

namespace App\Domains\CRM\Enums;

enum CRMStatus: string
{
    case New        = 'new';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';
    case Closed     = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New        => 'New',
            self::InProgress => 'In Progress',
            self::Resolved   => 'Resolved',
            self::Closed     => 'Closed',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Closed => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}