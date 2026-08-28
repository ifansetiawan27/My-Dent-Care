<?php

declare(strict_types=1);

namespace App\Domains\Notification\Services;

use App\Domains\WhatsApp\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationQueueService
{
    public function __construct(
        private readonly WhatsAppService $waService,
    ) {}

    /**
     * Queue a notification for sending.
     */
    public function queue(array $data): string
    {
        $id = Str::uuid()->toString();

        DB::table('notification_queue')->insert([
            'id' => $id,
            'channel' => $data['channel'] ?? 'whatsapp',
            'recipient' => $data['recipient'],
            'template' => $data['template'] ?? 'custom',
            'payload' => json_encode($data['payload'] ?? []),
            'status' => 'pending',
            'scheduled_at' => $data['scheduled_at'] ?? now(),
            'reference_id' => $data['reference_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * Process all pending notifications that are due.
     *
     * Rows are atomically claimed (pending -> processing) inside a
     * transaction so concurrent workers or overlapping scheduler runs
     * cannot send the same notification twice. Rows stuck in
     * "processing" for more than 10 minutes (crashed run) are
     * returned to "pending" for retry.
     */
    public function processDue(): array
    {
        $this->recoverStaleProcessing();

        $due = DB::transaction(function () {
            $rows = DB::table('notification_queue')
                ->where('status', 'pending')
                ->where('scheduled_at', '<=', now())
                ->where('retry_count', '<', 3)
                ->orderBy('scheduled_at')
                ->limit(50)
                ->lockForUpdate()
                ->get();

            if ($rows->isNotEmpty()) {
                DB::table('notification_queue')
                    ->whereIn('id', $rows->pluck('id'))
                    ->update([
                        'status' => 'processing',
                        'updated_at' => now(),
                    ]);
            }

            return $rows;
        });

        if ($due->isEmpty()) {
            return ['processed' => 0, 'success' => 0, 'failed' => 0];
        }

        $success = 0;
        $failed = 0;

        foreach ($due as $notification) {
            try {
                $result = $this->sendNotification($notification);

                if ($result['status'] === 'success') {
                    DB::table('notification_queue')
                        ->where('id', $notification->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'updated_at' => now(),
                        ]);
                    $success++;
                } else {
                    $this->markFailed($notification->id, $result['message'] ?? 'Unknown error');
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->markFailed($notification->id, $e->getMessage());
                $failed++;
            }
        }

        return [
            'processed' => $due->count(),
            'success' => $success,
            'failed' => $failed,
        ];
    }

    /**
     * Return notifications stuck in "processing" (crashed worker) back to
     * "pending" so they can be retried. retry_count is not incremented
     * because the send never completed.
     */
    private function recoverStaleProcessing(): void
    {
        DB::table('notification_queue')
            ->where('status', 'processing')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->update([
                'status' => 'pending',
                'updated_at' => now(),
            ]);
    }

    /**
     * Send a single notification.
     */
    private function sendNotification(object $notification): array
    {
        $payload = json_decode($notification->payload, true) ?? [];

        if ($notification->channel === 'whatsapp') {
            // For reminder template, use the appointment reminder formatter
            if ($notification->template === 'appointment_reminder') {
                return $this->waService->sendAppointmentReminder($payload);
            }

            // Generic message
            $message = $payload['message'] ?? 'Notification';
            return $this->waService->sendMessage($notification->recipient, $message);
        }

        return ['status' => 'error', 'message' => 'Unsupported channel: ' . $notification->channel];
    }

    /**
     * Mark a notification as failed and increment retry count.
     */
    private function markFailed(string $id, string $reason): void
    {
        DB::table('notification_queue')
            ->where('id', $id)
            ->update([
                'retry_count' => DB::raw('retry_count + 1'),
                'error_message' => $reason,
                'status' => DB::raw("CASE WHEN retry_count >= 2 THEN 'failed' ELSE 'pending' END"),
                'updated_at' => now(),
            ]);
    }

    /**
     * Get notification statistics.
     */
    public function getStats(): array
    {
        return [
            'pending' => DB::table('notification_queue')->where('status', 'pending')->count(),
            'sent' => DB::table('notification_queue')->where('status', 'sent')->count(),
            'failed' => DB::table('notification_queue')->where('status', 'failed')->count(),
            'cancelled' => DB::table('notification_queue')->where('status', 'cancelled')->count(),
        ];
    }
}
