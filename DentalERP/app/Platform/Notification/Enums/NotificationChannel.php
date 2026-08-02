<?php

declare(strict_types=1);

namespace App\Platform\Notification\Enums;

/**
 * NotificationChannel
 *
 * Delivery channels supported by the Notification Platform.
 * A single notification may target one or more channels.
 */
enum NotificationChannel: string
{
    case Email    = 'email';
    case WhatsApp = 'whatsapp';
    case Sms      = 'sms';
    case Push     = 'push';
    case InApp    = 'in_app';

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Email    => 'Email',
            self::WhatsApp => 'WhatsApp',
            self::Sms      => 'SMS',
            self::Push     => 'Push Notification',
            self::InApp    => 'In-App Notification',
        };
    }

    /**
     * Whether this channel is routed through the Integration Hub
     * (external third-party provider).
     */
    public function usesIntegrationHub(): bool
    {
        return match ($this) {
            self::WhatsApp, self::Sms, self::Push => true,
            default => false,
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
