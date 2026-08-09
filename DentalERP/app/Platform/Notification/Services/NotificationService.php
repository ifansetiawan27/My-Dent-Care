<?php

declare(strict_types=1);

namespace App\Platform\Notification\Services;

use App\Platform\Notification\Channels\EmailChannel;
use App\Platform\Notification\Channels\InAppChannel;
use App\Platform\Notification\Channels\PushChannel;
use App\Platform\Notification\Channels\SmsChannel;
use App\Platform\Notification\Channels\WhatsAppChannel;
use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\Contracts\NotificationServiceInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use App\Platform\Notification\Enums\NotificationStatus;
use App\Platform\Notification\Jobs\SendNotificationJob;
use App\Platform\Notification\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class NotificationService implements NotificationServiceInterface
{
    /** @var array<string, NotificationChannelInterface> */
    private array $drivers;

    public function __construct()
    {
        $this->drivers = [
            NotificationChannel::Email->value    => app(EmailChannel::class),
            NotificationChannel::WhatsApp->value => app(WhatsAppChannel::class),
            NotificationChannel::Sms->value      => app(SmsChannel::class),
            NotificationChannel::Push->value     => app(PushChannel::class),
            NotificationChannel::InApp->value    => app(InAppChannel::class),
        ];
    }

    public function send(NotificationMessageDTO $message): void
    {
        foreach ($message->channels as $channel) {
            Notification::create([
                'id'              => Notification::newUuid(),
                'organization_id' => $message->organizationId,
                'branch_id'       => $message->branchId,
                'notifiable_type' => $message->notifiableType,
                'notifiable_id'   => $message->notifiableId,
                'channel'         => $channel->value,
                'type'            => $message->type,
                'title'           => $message->title,
                'body'            => $message->body,
                'data'            => $message->data,
                'locale'          => $message->locale,
                'status'          => NotificationStatus::Pending->value,
            ]);
        }

        try {
            SendNotificationJob::dispatch($message, $this->drivers);
        } catch (\Throwable $e) {
            Log::error('[NotificationService::send] Queue dispatch failed.', [
                'exception' => $e::class,
                'type'      => $message->type,
            ]);
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
        $notification = Notification::find($notificationId);

        if (! $notification || $notification->channel !== 'in_app') {
            return false;
        }

        $user = Auth::user();
        if ($user && $notification->organization_id !== null
            && method_exists($user, 'getOrganizationId')
            && $notification->organization_id !== $user->getOrganizationId()) {
            return false;
        }

        if ($notification->read_at !== null) {
            return true;
        }

        $notification->update([
            'status'  => NotificationStatus::Read->value,
            'read_at' => now(),
        ]);

        return true;
    }
}
