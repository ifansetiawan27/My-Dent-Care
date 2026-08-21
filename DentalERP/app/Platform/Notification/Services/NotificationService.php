<?php

declare(strict_types=1);

namespace App\Platform\Notification\Services;

use App\Platform\Notification\Contracts\NotificationServiceInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Jobs\SendNotificationJob;
use App\Platform\Notification\Repositories\NotificationRepository;

/**
 * NotificationService
 *
 * Concrete implementation of NotificationServiceInterface.
 * Dispatches notifications asynchronously via Queue and fans out to each channel.
 */
final class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepository $repository
    ) {
    }

    public function send(NotificationMessageDTO $message): void
    {
        // Queue one job per channel
        foreach ($message->channels as $channel) {
            dispatch(new SendNotificationJob($message, $channel));
        }
    }

    public function sendMany(array $messages): void
    {
        foreach ($messages as $message) {
            $this->send($message);
        }
    }

    public function markAsRead(string $notificationId): bool
    {
        return $this->repository->markAsRead($notificationId);
    }
}
