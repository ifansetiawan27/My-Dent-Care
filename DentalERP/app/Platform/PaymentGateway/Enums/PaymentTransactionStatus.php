<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\Enums;

/**
 * PaymentTransactionStatus
 *
 * Normalized lifecycle status of a payment transaction, unified across all providers.
 * Provider-specific statuses are mapped into these canonical values.
 */
enum PaymentTransactionStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Paid       = 'paid';
    case Failed     = 'failed';
    case Expired    = 'expired';
    case Refunded   = 'refunded';
    case Cancelled  = 'cancelled';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Processing => 'Processing',
            self::Paid       => 'Paid',
            self::Failed     => 'Failed',
            self::Expired    => 'Expired',
            self::Refunded   => 'Refunded',
            self::Cancelled  => 'Cancelled',
        };
    }

    /**
     * Whether this status is terminal (no further transitions expected).
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Paid, self::Failed, self::Expired, self::Refunded, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * Whether this status represents a successful payment.
     */
    public function isSuccessful(): bool
    {
        return $this === self::Paid;
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
