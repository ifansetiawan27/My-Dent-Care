<?php

declare(strict_types=1);

use App\Domains\Appointment\Services\ReminderService\AppointmentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createReminderAppointment(string $orgId, string $patientId, string $doctorId, array $overrides = []): string
{
    $id = (string) Str::orderedUuid();

    DB::table('appointments')->insert(array_merge([
        'id' => $id,
        'organization_id' => $orgId,
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'scheduled_at' => now()->addMinutes(30),
        'status' => 'scheduled',
        'type' => 'checkup',
        'reminder_minutes' => 60,
        'reminder_sent' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides, ['id' => $id]));

    return $id;
}

beforeEach(function (): void {
    $this->orgId = (string) Str::orderedUuid();
    $this->patientId = (string) Str::orderedUuid();
    $this->doctorId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-RMD-01', 'company_name' => 'Reminder Test Org',
        'email' => 'rmd@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'patient_code' => 'PAT-RMD-01', 'full_name' => 'Budi Santoso',
        'organization_id' => $this->orgId, 'phone' => '081234567890',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('doctors')->insert([
        'id' => $this->doctorId, 'doctor_code' => 'DOC-RMD-01', 'full_name' => 'drg. Ani Wijaya',
        'organization_id' => $this->orgId,
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(AppointmentReminderService::class);
});

it('queues a reminder when the reminder time has arrived', function (): void {
    $id = createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, [
        'scheduled_at' => now()->addMinutes(30),
        'reminder_minutes' => 60,
    ]);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(1);

    $queued = DB::table('notification_queue')->where('reference_id', $id)->first();
    expect($queued)->not->toBeNull();
    expect($queued->channel)->toBe('whatsapp');
    expect($queued->template)->toBe('appointment_reminder');
    // Digits-only at queue time; 08xx -> 628xx normalization happens in WhatsAppService::sendMessage.
    expect($queued->recipient)->toBe('081234567890');

    expect((bool) DB::table('appointments')->where('id', $id)->first()->reminder_sent)->toBeTrue();
});

it('does not queue when reminder time has not arrived yet', function (): void {
    createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, [
        'scheduled_at' => now()->addHours(5),
        'reminder_minutes' => 60,
    ]);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(0);
    expect(DB::table('notification_queue')->count())->toBe(0);
});

it('still queues overdue reminders after scheduler downtime', function (): void {
    // Reminder was due 40 minutes ago but the scheduler was down;
    // the appointment itself is still 20 minutes in the future.
    $id = createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, [
        'scheduled_at' => now()->addMinutes(20),
        'reminder_minutes' => 60,
    ]);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(1);
    expect((bool) DB::table('appointments')->where('id', $id)->first()->reminder_sent)->toBeTrue();
});

it('skips appointments without patient phone number', function (): void {
    DB::table('patients')->where('id', $this->patientId)->update(['phone' => null]);

    createReminderAppointment($this->orgId, $this->patientId, $this->doctorId);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(0);
    expect($result['skipped'])->toBe(1);
    expect(DB::table('notification_queue')->count())->toBe(0);
});

it('ignores cancelled appointments', function (): void {
    createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, ['status' => 'cancelled']);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(0);
});

it('ignores appointments that already started', function (): void {
    createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, [
        'scheduled_at' => now()->subMinutes(10),
    ]);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(0);
});

it('does not requeue reminders that were already sent', function (): void {
    createReminderAppointment($this->orgId, $this->patientId, $this->doctorId, ['reminder_sent' => true]);

    $result = $this->service->processReminders();

    expect($result['queued'])->toBe(0);
    expect(DB::table('notification_queue')->count())->toBe(0);
});
