<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Enums;

enum ProcurementStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Ordered   = 'ordered';
    case Received  = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Approved  => 'Approved',
            self::Ordered   => 'Ordered',
            self::Received  => 'Received',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Received, self::Cancelled => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}