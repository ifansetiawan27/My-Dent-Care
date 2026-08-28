<?php

declare(strict_types=1);

use App\Domains\Notification\Services\NotificationQueueService;
use App\Domains\WhatsApp\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.bridge_url' => 'http://bridge.test', 'whatsapp.timeout' => 5]);
    $this->service = new NotificationQueueService(new WhatsAppService());
});

function queueNotification(array $overrides = []): string
{
    $id = \Illuminate\Support\Str::uuid()->toString();

    DB::table('notification_queue')->insert(array_merge([
        'id' => $id,
        'channel' => 'whatsapp',
        'recipient' => '6281234567890',
        'template' => 'custom',
        'payload' => json_encode(['message' => 'Test']),
        'status' => 'pending',
        'scheduled_at' => now()->subMinute(),
        'retry_count' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides, ['id' => $id]));

    return $id;
}

it('queues a notification as pending', function (): void {
    $id = $this->service->queue([
        'channel' => 'whatsapp',
        'recipient' => '6281234567890',
        'template' => 'appointment_reminder',
        'payload' => ['patient' => ['full_name' => 'Budi']],
        'reference_id' => 'apt-1',
    ]);

    $row = DB::table('notification_queue')->where('id', $id)->first();
    expect($row->status)->toBe('pending');
    expect($row->template)->toBe('appointment_reminder');
    expect($row->reference_id)->toBe('apt-1');
});

it('processes due notifications and marks them sent', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success']),
    ]);

    $id = queueNotification();

    $result = $this->service->processDue();

    expect($result)->toBe(['processed' => 1, 'success' => 1, 'failed' => 0]);
    expect(DB::table('notification_queue')->where('id', $id)->first()->status)->toBe('sent');
});

it('does not process notifications scheduled in the future', function (): void {
    Http::fake();

    queueNotification(['scheduled_at' => now()->addHour()]);

    $result = $this->service->processDue();

    expect($result['processed'])->toBe(0);
    Http::assertNothingSent();
});

it('retries failed notifications and marks failed after three attempts', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'error', 'message' => 'boom'], 500),
    ]);

    $id = queueNotification();

    // Attempt 1 and 2: back to pending with incremented retry_count.
    $this->service->processDue();
    $row = DB::table('notification_queue')->where('id', $id)->first();
    expect($row->status)->toBe('pending');
    expect($row->retry_count)->toBe(1);

    $this->service->processDue();
    $row = DB::table('notification_queue')->where('id', $id)->first();
    expect($row->status)->toBe('pending');
    expect($row->retry_count)->toBe(2);

    // Attempt 3: permanently failed.
    $this->service->processDue();
    $row = DB::table('notification_queue')->where('id', $id)->first();
    expect($row->status)->toBe('failed');
    expect($row->retry_count)->toBe(3);
    expect($row->error_message)->not->toBeNull();
});

it('claims rows as processing so overlapping runs cannot double-send', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success']),
    ]);

    $id = queueNotification();

    // Simulate a concurrent worker having claimed the row already.
    DB::table('notification_queue')->where('id', $id)->update([
        'status' => 'processing',
        'updated_at' => now(),
    ]);

    $result = $this->service->processDue();

    expect($result['processed'])->toBe(0);
    Http::assertNothingSent();
});

it('recovers rows stuck in processing for more than ten minutes', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success']),
    ]);

    $id = queueNotification([
        'status' => 'processing',
        'updated_at' => now()->subMinutes(15),
    ]);

    $result = $this->service->processDue();

    expect($result)->toBe(['processed' => 1, 'success' => 1, 'failed' => 0]);
    expect(DB::table('notification_queue')->where('id', $id)->first()->status)->toBe('sent');
});

it('reports queue statistics', function (): void {
    queueNotification(['status' => 'sent', 'scheduled_at' => now()->subHour()]);
    queueNotification(['status' => 'failed', 'retry_count' => 3, 'scheduled_at' => now()->subHour()]);
    queueNotification();

    $stats = $this->service->getStats();

    expect($stats['pending'])->toBe(1);
    expect($stats['sent'])->toBe(1);
    expect($stats['failed'])->toBe(1);
});
