<?php

declare(strict_types=1);

namespace App\Platform\Notification\Channels;

use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;

final class SmsChannel implements NotificationChannelInterface
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Sms;
    }

    public function deliver(NotificationMessageDTO $message): bool
    {
        return true;
    }

    public function isAvailableFor(string $organizationId): bool
    {
        return false;
    }
}
