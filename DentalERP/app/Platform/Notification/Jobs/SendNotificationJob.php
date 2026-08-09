<?php

declare(strict_types=1);

namespace App\Platform\Notification\Jobs;

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use App\Platform\Notification\Enums\NotificationStatus;
use App\Platform\Notification\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly NotificationMessageDTO $message,
        /** @var array<string, NotificationChannelInterface> */
        private readonly array $drivers = [],
    ) {}

    public function handle(
        AuditServiceInterface $audit,
        LoggerServiceInterface $logger,
    ): void {
        foreach ($this->message->channels as $channel) {
            $this->deliverToChannel($channel, $audit, $logger);
        }
    }

    private function deliverToChannel(
        NotificationChannel $channel,
        AuditServiceInterface $audit,
        LoggerServiceInterface $logger,
    ): void {
        $notification = Notification::where('notifiable_type', $this->message->notifiableType)
            ->where('notifiable_id', $this->message->notifiableId)
            ->where('organization_id', $this->message->organizationId)
            ->where('channel', $channel->value)
            ->where('status', NotificationStatus::Pending->value)
            ->latest()
            ->first();

        if (! $notification) {
            return;
        }

        $driver = $this->drivers[$channel->value] ?? null;

        if (! $driver || ! $driver->isAvailableFor((string) $this->message->organizationId)) {
            $logger->warning('[Notification::send] Channel unavailable.', [
                'channel'         => $channel->value,
                'organization_id' => $this->message->organizationId,
            ]);

            return;
        }

        if ($driver->deliver($this->message)) {
            $notification->update([
                'status'  => NotificationStatus::Sent->value,
                'sent_at' => now(),
            ]);

            $audit->log(
                action:        AuditAction::Create,
                module:        'notification',
                auditableType: Notification::class,
                auditableId:   $notification->id,
                oldValue:      [],
                newValue:      ['channel' => $channel->value, 'status' => 'sent'],
            );
        } else {
            $this->markAsFailed($notification, $channel, 'Delivery failed.', $audit, $logger);
        }
    }

    private function markAsFailed(
        Notification $notification,
        NotificationChannel $channel,
        string $reason,
        AuditServiceInterface $audit,
        LoggerServiceInterface $logger,
    ): void {
        $notification->update([
            'status'        => NotificationStatus::Failed->value,
            'failed_reason' => $reason,
        ]);

        $audit->log(
            action:        AuditAction::Update,
            module:        'notification',
            auditableType: Notification::class,
            auditableId:   $notification->id,
            oldValue:      ['status' => 'pending'],
            newValue:      ['status' => 'failed', 'channel' => $channel->value],
        );

        $logger->error('[Notification::send] Delivery failed.', [
            'notification_id'  => $notification->id,
            'channel'          => $channel->value,
            'reason'           => $reason,
            'organization_id'  => $notification->organization_id,
        ]);
    }
}
