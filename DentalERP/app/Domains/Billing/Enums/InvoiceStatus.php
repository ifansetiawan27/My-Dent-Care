<?php

declare(strict_types=1);

namespace App\Domains\Billing\Enums;

enum InvoiceStatus: string
{
    case Draft    = 'draft';
    case Sent     = 'sent';
    case Paid     = 'paid';
    case Overdue  = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Paid     => 'Paid',
            self::Overdue  => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Paid, self::Cancelled => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}