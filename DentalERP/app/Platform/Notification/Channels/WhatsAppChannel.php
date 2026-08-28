<?php

declare(strict_types=1);

namespace App\Platform\Notification\Channels;

use App\Domains\WhatsApp\Services\WhatsAppService;
use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
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
        if (!$this->isAvailableFor('')) {
            Log::warning('WhatsApp channel not available, skipping delivery.');
            return false;
        }

        $phone = $message->to;
        $text = $message->content;

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
}
