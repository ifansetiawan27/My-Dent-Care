<?php

declare(strict_types=1);

namespace App\Platform\Webhook\Enums;

/**
 * WebhookDirection
 *
 * Direction of a webhook relative to this platform.
 */
enum WebhookDirection: string
{
    case Outgoing = 'outgoing'; // We send events to an external subscriber.
    case Incoming = 'incoming'; // We receive events from an external provider.

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Outgoing => 'Outgoing',
            self::Incoming => 'Incoming',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
