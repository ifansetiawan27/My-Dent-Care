<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * PaymentStatus Enum — shared across Finance and Appointment domains.
 */
enum PaymentStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Partial   = 'partial';
    case Cancelled = 'cancelled';
    case Refunded  = 'refunded';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Paid      => 'Paid',
            self::Partial   => 'Partial',
            self::Cancelled => 'Cancelled',
            self::Refunded  => 'Refunded',
        };
    }

    /**
     * Determine whether this status represents a completed payment.
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Paid, self::Cancelled, self::Refunded => true,
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
