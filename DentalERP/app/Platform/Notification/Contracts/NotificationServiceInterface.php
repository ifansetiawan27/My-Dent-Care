<?php

declare(strict_types=1);

namespace App\Platform\Notification\Contracts;

use App\Platform\Notification\DTO\NotificationMessageDTO;

/**
 * NotificationServiceInterface
 *
 * The single contract for sending notifications across the entire ERP.
 * Implementations MUST dispatch through Laravel Queue (non-blocking) and
 * fan out to each requested channel via NotificationChannelInterface drivers.
 *
 * Platform rule: Domains depend on this interface only — never on
 * Email/WhatsApp/SMS/Push providers directly.
 */
interface NotificationServiceInterface
{
    /**
     * Queue a notification for delivery to all requested channels.
     *
     * @param  NotificationMessageDTO $message
     * @return void
     */
    public function send(NotificationMessageDTO $message): void;

    /**
     * Queue the same notification to multiple recipients.
     *
     * @param  array<int, NotificationMessageDTO> $messages
     * @return void
     */
    public function sendMany(array $messages): void;

    /**
     * Mark an in-app notification as read.
     *
     * @param  string $notificationId
     * @return bool
     */
    public function markAsRead(string $notificationId): bool;
}
