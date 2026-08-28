<?php

declare(strict_types=1);

namespace App\Platform\Notification\Channels;

use App\Domains\WhatsApp\Services\WhatsAppService;
use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class WhatsAppChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly WhatsAppService $waService,
    ) {}

    public function channel(): NotificationChannel
    {
        return NotificationChannel::WhatsApp;
    }

    public function deliver(NotificationMessageDTO $message): bool
    {
        if (!$this->isAvailableFor($message->organizationId ?? '')) {
            Log::warning('WhatsApp channel not available, skipping delivery.');
            return false;
        }

        $phone = $this->resolvePhone($message);
        if ($phone === null) {
            Log::warning('WhatsApp delivery skipped: no phone number resolvable.', [
                'notifiable_type' => $message->notifiableType,
                'notifiable_id'   => $message->notifiableId,
            ]);
            return false;
        }

        $text = $message->title !== ''
            ? $message->title . "\n" . $message->body
            : $message->body;

        $result = $this->waService->sendMessage($phone, $text);

        if ($result['status'] === 'success') {
            Log::info('WhatsApp delivered to ' . $phone, ['message_id' => $result['message_id'] ?? null]);
            return true;
        }

        Log::error('WhatsApp delivery failed', ['phone' => $phone, 'error' => $result['message'] ?? 'unknown']);
        return false;
    }

    public function isAvailableFor(string $organizationId): bool
    {
        $status = $this->waService->getSessionStatus();
        return $status['status'] === 'connected';
    }

    /**
     * Resolve the recipient phone number. Prefers an explicit phone in the
     * message data payload, then falls back to the notifiable's stored phone.
     */
    private function resolvePhone(NotificationMessageDTO $message): ?string
    {
        $phone = $message->data['phone'] ?? null;
        if (is_string($phone) && $phone !== '') {
            return $phone;
        }

        $record = match (class_basename($message->notifiableType)) {
            'Patient' => DB::table('patients')->find($message->notifiableId),
            'User'    => DB::table('users')->find($message->notifiableId),
            default   => null,
        };

        return isset($record->phone) && $record->phone !== '' ? $record->phone : null;
    }
}
