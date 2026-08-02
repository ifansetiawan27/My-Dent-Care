<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * InvoiceStatus Enum — used by Finance domain.
 */
enum InvoiceStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Paid      = 'paid';
    case Overdue   = 'overdue';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Sent      => 'Sent',
            self::Paid      => 'Paid',
            self::Overdue   => 'Overdue',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Determine whether this status represents a terminal state.
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Paid, self::Cancelled => true,
            default => false,
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
