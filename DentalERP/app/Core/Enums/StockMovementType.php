<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * StockMovementType — Canonical Core Enum
 *
 * Classifies every inventory stock movement.
 * Used by Inventory domain and referenced by Pharmacy, Procurement.
 */
enum StockMovementType: string
{
    case In         = 'in';         // Stock received (purchase, transfer in)
    case Out        = 'out';        // Stock consumed (usage, transfer out)
    case Adjustment = 'adjustment'; // Manual stock correction
    case Transfer   = 'transfer';   // Inter-branch transfer
    case Return     = 'return';     // Returned to supplier

    public function label(): string
    {
        return match ($this) {
            self::In         => 'Stock In',
            self::Out        => 'Stock Out',
            self::Adjustment => 'Adjustment',
            self::Transfer   => 'Transfer',
            self::Return     => 'Return',
        };
    }

    /**
     * Whether this movement increases stock quantity.
     */
    public function increasesStock(): bool
    {
        return match ($this) {
            self::In, self::Return => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
