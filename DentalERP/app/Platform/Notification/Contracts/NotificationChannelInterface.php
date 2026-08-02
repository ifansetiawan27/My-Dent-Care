<?php

declare(strict_types=1);

namespace App\Platform\Notification\Contracts;

use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;

/**
 * NotificationChannelInterface
 *
 * Contract for a single delivery channel driver (Email, WhatsApp, SMS, Push, In-App).
 * The Notification Platform resolves the correct driver per channel and delegates delivery.
 *
 * Open/Closed: adding a new channel means adding a new driver implementing this
 * interface — no existing code is modified.
 */
interface NotificationChannelInterface
{
    /**
     * The channel this driver handles.
     */
    public function channel(): NotificationChannel;

    /**
     * Deliver the notification through this channel.
     * Called from inside a queued job.
     *
     * @param  NotificationMessageDTO $message
     * @return bool  True when accepted by the provider.
     */
    public function deliver(NotificationMessageDTO $message): bool;

    /**
     * Whether this channel is enabled/configured for the given organization.
     *
     * @param  string $organizationId
     * @return bool
     */
    public function isAvailableFor(string $organizationId): bool;
}
