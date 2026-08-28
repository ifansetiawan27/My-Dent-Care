<?php

declare(strict_types=1);

use App\Domains\WhatsApp\Services\WhatsAppService;
use App\Platform\Notification\Channels\WhatsAppChannel;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.bridge_url' => 'http://bridge.test', 'whatsapp.timeout' => 5]);

    $this->channel = new WhatsAppChannel(new WhatsAppService());
});

function fakeBridge(string $status = 'connected'): void
{
    Http::fake([
        'bridge.test/api/status' => Http::response(['status' => $status]),
        'bridge.test/api/send' => Http::response(['status' => 'success', 'message_id' => 'wa-1']),
    ]);
}

function waMessage(array $overrides = []): NotificationMessageDTO
{
    return new NotificationMessageDTO(
        type: $overrides['type'] ?? 'appointment_reminder',
        notifiableType: $overrides['notifiableType'] ?? 'Patient',
        notifiableId: $overrides['notifiableId'] ?? '1',
        channels: [NotificationChannel::WhatsApp],
        title: $overrides['title'] ?? '',
        body: $overrides['body'] ?? 'Reminder body',
        data: $overrides['data'] ?? [],
    );
}

function assertSendCall(callable $check): void
{
    Http::assertSent(function ($request) use ($check) {
        return str_contains($request->url(), '/api/send') && $check($request);
    });
}

it('delivers using explicit phone from data payload', function (): void {
    fakeBridge();

    $result = $this->channel->deliver(waMessage([
        'data' => ['phone' => '081234567890'],
    ]));

    expect($result)->toBeTrue();
    assertSendCall(fn ($request) => $request['phone'] === '6281234567890'
        && str_contains($request['message'], 'Reminder body'));
});

it('resolves phone from patient notifiable when not in data', function (): void {
    fakeBridge();

    $orgId = (string) Str::orderedUuid();
    $patientId = (string) Str::orderedUuid();
    DB::table('organizations')->insert([
        'id' => $orgId, 'company_code' => 'ORG-WAC', 'company_name' => 'WA Channel Org',
        'email' => 'wac@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('patients')->insert([
        'id' => $patientId, 'patient_code' => 'PAT-WAC', 'full_name' => 'Budi',
        'organization_id' => $orgId, 'phone' => '08199998888',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $result = $this->channel->deliver(waMessage([
        'notifiableType' => 'Patient',
        'notifiableId' => $patientId,
    ]));

    expect($result)->toBeTrue();
    assertSendCall(fn ($request) => $request['phone'] === '628199998888');
});

it('prepends title to body when title is present', function (): void {
    fakeBridge();

    $this->channel->deliver(waMessage([
        'title' => 'Judul',
        'body' => 'Isi pesan',
        'data' => ['phone' => '081234567890'],
    ]));

    assertSendCall(fn ($request) => str_contains($request['message'], "Judul\nIsi pesan"));
});

it('returns false without sending when bridge session is not connected', function (): void {
    fakeBridge('disconnected');

    $result = $this->channel->deliver(waMessage(['data' => ['phone' => '081234567890']]));

    expect($result)->toBeFalse();
    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/status'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/send'));
});

it('returns false when no phone can be resolved', function (): void {
    fakeBridge();

    $result = $this->channel->deliver(waMessage([
        'notifiableType' => 'Patient',
        'notifiableId' => (string) Str::orderedUuid(), // nonexistent
    ]));

    expect($result)->toBeFalse();
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/send'));
});
