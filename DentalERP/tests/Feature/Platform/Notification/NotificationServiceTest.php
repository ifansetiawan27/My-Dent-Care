<?php

declare(strict_types=1);

use App\Platform\Notification\Channels\EmailChannel;
use App\Platform\Notification\Channels\InAppChannel;
use App\Platform\Notification\Channels\PushChannel;
use App\Platform\Notification\Channels\SmsChannel;
use App\Platform\Notification\Channels\WhatsAppChannel;
use App\Platform\Notification\Contracts\NotificationServiceInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use App\Platform\Notification\Enums\NotificationStatus;
use App\Platform\Notification\Jobs\SendNotificationJob;
use App\Platform\Notification\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId    = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $this->notifiableId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-NOT-01', 'company_name' => 'Notification Test Org',
        'email' => 'notif@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-NOT-01',
        'branch_name' => 'Notification Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

it('send creates pending notification records — one per channel', function (): void {
    $message = new NotificationMessageDTO(
        type:           'appointment_reminder',
        notifiableType: 'App\\Models\\User',
        notifiableId:   $this->notifiableId,
        channels:       [NotificationChannel::Email, NotificationChannel::Sms],
        title:          'Reminder',
        body:           'Your appointment is tomorrow.',
        organizationId: $this->orgId,
        branchId:       $this->branchId,
    );

    app(NotificationServiceInterface::class)->send($message);

    $records = Notification::all();
    expect($records)->toHaveCount(2);
    expect($records->pluck('channel')->toArray())->toBe(['email', 'sms']);
    expect($records->every(fn ($r) => $r->status === 'pending'))->toBeTrue();
});

it('send dispatches SendNotificationJob to queue', function (): void {
    $message = new NotificationMessageDTO(
        type:           'test',
        notifiableType: 'User',
        notifiableId:   $this->notifiableId,
        channels:       [NotificationChannel::Email],
        title:          'Test',
        body:           'Test body.',
        organizationId: $this->orgId,
    );

    app(NotificationServiceInterface::class)->send($message);

    Queue::assertPushed(SendNotificationJob::class);
});

it('sendMany dispatches for each message', function (): void {
    $messages = [
        new NotificationMessageDTO(
            type: 'test', notifiableType: 'User', notifiableId: (string) Str::orderedUuid(),
            channels: [NotificationChannel::Email], title: 'T1', body: 'B1', organizationId: $this->orgId,
        ),
        new NotificationMessageDTO(
            type: 'test', notifiableType: 'User', notifiableId: (string) Str::orderedUuid(),
            channels: [NotificationChannel::Sms], title: 'T2', body: 'B2', organizationId: $this->orgId,
        ),
    ];

    app(NotificationServiceInterface::class)->sendMany($messages);

    expect(Notification::count())->toBe(2);
});

it('markAsRead returns false for non-in-app channel', function (): void {
    $notification = Notification::create([
        'id'              => Notification::newUuid(),
        'organization_id' => $this->orgId,
        'notifiable_type' => 'User',
        'notifiable_id'   => $this->notifiableId,
        'channel'         => 'email',
        'type'            => 'test',
        'title'           => 'Test',
        'body'            => 'Test body.',
        'status'          => 'pending',
    ]);

    $result = app(NotificationServiceInterface::class)->markAsRead($notification->id);
    expect($result)->toBeFalse();
});

it('markAsRead sets read_at and status for in-app notification', function (): void {
    $notification = Notification::create([
        'id'              => Notification::newUuid(),
        'organization_id' => $this->orgId,
        'notifiable_type' => 'User',
        'notifiable_id'   => $this->notifiableId,
        'channel'         => 'in_app',
        'type'            => 'test',
        'title'           => 'Test',
        'body'            => 'Test body.',
        'status'          => 'pending',
    ]);

    $result = app(NotificationServiceInterface::class)->markAsRead($notification->id);
    expect($result)->toBeTrue();

    $notification->refresh();
    expect($notification->status)->toBe('read');
    expect($notification->read_at)->not->toBeNull();
});

it('markAsRead returns true for already read notification', function (): void {
    $notification = Notification::create([
        'id'              => Notification::newUuid(),
        'organization_id' => $this->orgId,
        'notifiable_type' => 'User',
        'notifiable_id'   => $this->notifiableId,
        'channel'         => 'in_app',
        'type'            => 'test',
        'title'           => 'Test',
        'body'            => 'Test body.',
        'status'          => 'read',
        'read_at'         => now(),
    ]);

    $result = app(NotificationServiceInterface::class)->markAsRead($notification->id);
    expect($result)->toBeTrue();
});

it('markAsRead returns false for nonexistent notification', function (): void {
    $result = app(NotificationServiceInterface::class)->markAsRead((string) Str::orderedUuid());
    expect($result)->toBeFalse();
});

it('Notification model extends BaseModel with HasUuid, HasAudit, SoftDeletes', function (): void {
    $traits = class_uses_recursive(Notification::class);

    expect($traits)->toHaveKey('App\Core\Traits\HasUuid');
    expect($traits)->toHaveKey('App\Core\Traits\HasAudit');
    expect($traits)->toHaveKey('Illuminate\Database\Eloquent\SoftDeletes');
    expect((new Notification())->getTable())->toBe('notifications');
});

it('SendNotificationJob has 3 retry attempts with exponential backoff', function (): void {
    $job = new SendNotificationJob(
        new NotificationMessageDTO(
            type: 'test', notifiableType: 'U', notifiableId: '1',
            channels: [NotificationChannel::Email], title: 'T', body: 'B',
        ),
    );

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([60, 300, 900]);
});
