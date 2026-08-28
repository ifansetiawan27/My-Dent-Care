<?php

declare(strict_types=1);

use App\Domains\WhatsApp\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['whatsapp.bridge_url' => 'http://bridge.test', 'whatsapp.timeout' => 5]);
    $this->service = new WhatsAppService();
});

it('normalizes indonesian phone numbers before sending', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success', 'message_id' => 'wa-1']),
    ]);

    $result = $this->service->sendMessage('0812-3456-7890', 'Halo');

    expect($result['status'])->toBe('success');
    Http::assertSent(function ($request) {
        return $request['phone'] === '6281234567890' && $request['message'] === 'Halo';
    });
});

it('keeps country code when phone already starts with 62', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success']),
    ]);

    $this->service->sendMessage('+62 812 3456 7890', 'Halo');

    Http::assertSent(fn ($request) => $request['phone'] === '6281234567890');
});

it('returns error payload when bridge is unreachable', function (): void {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('refused');
    });

    $result = $this->service->sendMessage('081234567890', 'Halo');

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Bridge not reachable');
});

it('returns error payload when bridge responds unsuccessfully', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['message' => 'Session not connected'], 400),
    ]);

    $result = $this->service->sendMessage('081234567890', 'Halo');

    expect($result['status'])->toBe('error');
    expect($result['message'])->toBe('Session not connected');
});

it('returns disconnected status when bridge is unreachable', function (): void {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('refused');
    });

    $status = $this->service->getSessionStatus();

    expect($status['status'])->toBe('disconnected');
});

it('syncs session record into whatsapp_sessions table', function (): void {
    Http::fake([
        'bridge.test/api/status' => Http::response(['status' => 'connected', 'phone' => '6281234567890']),
    ]);

    $this->service->getSessionStatus();

    $session = DB::table('whatsapp_sessions')
        ->where('display_name', WhatsAppService::DEFAULT_SESSION_NAME)
        ->first();

    expect($session)->not->toBeNull();
    expect($session->status)->toBe('connected');
    expect($session->phone_number)->toBe('6281234567890');
    expect($session->connected_at)->not->toBeNull();
});

it('updates existing session record instead of duplicating', function (): void {
    Http::fake([
        'bridge.test/api/status' => Http::sequence()
            ->push(['status' => 'connected', 'phone' => '6281234567890'])
            ->push(['status' => 'disconnected']),
    ]);

    $this->service->getSessionStatus();
    $this->service->getSessionStatus();

    expect(DB::table('whatsapp_sessions')->count())->toBe(1);
    expect(DB::table('whatsapp_sessions')->first()->status)->toBe('disconnected');
});

it('builds appointment reminder message with patient and doctor names', function (): void {
    Http::fake([
        'bridge.test/api/send' => Http::response(['status' => 'success']),
    ]);

    $result = $this->service->sendAppointmentReminder([
        'patient' => ['full_name' => 'Budi Santoso', 'phone' => '081234567890'],
        'doctor' => ['full_name' => 'drg. Ani Wijaya'],
        'scheduled_at' => now()->addHours(2)->toDateTimeString(),
        'type' => 'checkup',
    ]);

    expect($result['status'])->toBe('success');
    Http::assertSent(function ($request) {
        return str_contains($request['message'], 'Budi Santoso')
            && str_contains($request['message'], 'drg. Ani Wijaya')
            && str_contains($request['message'], 'Check-up');
    });
});

it('fails reminder when patient phone is missing', function (): void {
    Http::fake();

    $result = $this->service->sendAppointmentReminder([
        'patient' => ['full_name' => 'Budi Santoso'],
        'scheduled_at' => now()->addHours(2)->toDateTimeString(),
    ]);

    expect($result['status'])->toBe('error');
    Http::assertNothingSent();
});
