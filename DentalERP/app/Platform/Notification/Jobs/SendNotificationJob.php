<?php

declare(strict_types=1);

namespace App\Platform\Notification\Jobs;

use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use App\Platform\Notification\Models\Notification;
use App\Platform\Notification\Repositories\NotificationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * SendNotificationJob
 *
 * Asynchronously sends a notification through a single channel.
 * Multiple jobs are dispatched by NotificationService (one per channel).
 */
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    public function __construct(
        private readonly NotificationMessageDTO $message,
        private readonly NotificationChannel $channel
    ) {
    }

    public function handle(NotificationRepository $repository): void
    {
        // Create notification record
        $notificationId = (string) Str::orderedUuid();
        
        try {
            $notification = $repository->create([
                'id' => $notificationId,
                'organization_id' => $this->message->organizationId,
                'branch_id' => $this->message->branchId,
                'notifiable_type' => $this->message->notifiableType,
                'notifiable_id' => $this->message->notifiableId,
                'type' => $this->message->type,
                'channel' => $this->channel->value,
                'status' => 'pending',
                'subject' => $this->message->title,
                'body' => $this->message->body,
                'data' => $this->message->data,
                'retry_count' => 0,
                'created_by' => Auth::id(),
            ]);

            // Dispatch to channel-specific handler
            $this->sendViaChannel($notification);

            // Mark as sent
            $repository->markAsSent($notificationId);
            
        } catch (\Exception $e) {
            // Mark as failed
            $repository->markAsFailed($notificationId, $e->getMessage());
            
            // Re-throw to trigger queue retry
            throw $e;
        }
    }

    private function sendViaChannel(Notification $notification): void
    {
        // Channel-specific implementation would go here
        // For now, we just mark in_app notifications as sent immediately
        // Other channels (email, whatsapp, sms, push) would integrate with external providers
        
        match ($this->channel) {
            NotificationChannel::InApp => $this->sendInApp($notification),
            NotificationChannel::Email => $this->sendEmail($notification),
            NotificationChannel::Whatsapp => $this->sendWhatsapp($notification),
            NotificationChannel::Sms => $this->sendSms($notification),
            NotificationChannel::Push => $this->sendPush($notification),
        };
    }

    private function sendInApp(Notification $notification): void
    {
        // In-app notifications are already persisted
        // Real-time broadcasting would happen here via Laravel Echo/Reverb
    }

    private function sendEmail(Notification $notification): void
    {
        // TODO: Integrate with Mail driver (SMTP, Mailgun, SES)
        // Mail::to($recipient)->send(new NotificationMail($notification));
    }

    private function sendWhatsapp(Notification $notification): void
    {
        // TODO: Integrate with WhatsApp Business API via IntegrationHub
    }

    private function sendSms(Notification $notification): void
    {
        // TODO: Integrate with SMS provider (Twilio, Vonage)
    }

    private function sendPush(Notification $notification): void
    {
        // TODO: Integrate with FCM for push notifications
    }
}
