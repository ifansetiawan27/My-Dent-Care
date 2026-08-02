<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\Enums;

/**
 * PaymentProvider
 *
 * Supported payment gateway providers.
 * Each provider is implemented behind PaymentProviderInterface.
 */
enum PaymentProvider: string
{
    case Midtrans = 'midtrans';
    case Xendit   = 'xendit';
    case Doku     = 'doku';
    case Manual   = 'manual'; // Cash / manual bank transfer reconciliation.

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Midtrans => 'Midtrans',
            self::Xendit   => 'Xendit',
            self::Doku     => 'DOKU',
            self::Manual   => 'Manual',
        };
    }

    /**
     * Whether this provider processes payments online (via API).
     */
    public function isOnline(): bool
    {
        return $this !== self::Manual;
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
