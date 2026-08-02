<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * PaymentMethodType — Canonical Core Enum
 *
 * Classifies payment methods by their processing type.
 * Used by Finance, Billing, and the Payment Gateway Platform.
 */
enum PaymentMethodType: string
{
    case Cash      = 'cash';
    case Transfer  = 'transfer';
    case Card      = 'card';
    case Ewallet   = 'ewallet';
    case Insurance = 'insurance';

    public function label(): string
    {
        return match ($this) {
            self::Cash      => 'Cash',
            self::Transfer  => 'Bank Transfer',
            self::Card      => 'Card (Debit/Credit)',
            self::Ewallet   => 'E-Wallet',
            self::Insurance => 'Insurance',
        };
    }

    /**
     * Whether this method requires online gateway processing.
     */
    public function requiresGateway(): bool
    {
        return match ($this) {
            self::Card, self::Ewallet => true,
            default => false,
        };
    }

    /**
     * Whether this method is typically settled immediately.
     */
    public function isImmediateSettlement(): bool
    {
        return match ($this) {
            self::Cash, self::Card, self::Ewallet => true,
            default => false,
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
