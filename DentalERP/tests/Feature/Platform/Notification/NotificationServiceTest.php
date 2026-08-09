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
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Queue::fake();
});

it('send creates pending notification records — one per channel', function (): void {
    $message = new NotificationMessageDTO(
        type:           'appointment_reminder',
        notifiableType: 'App\\Models\\User',
        notifiableId:   'user-1',
        channels:       [NotificationChannel::Email, NotificationChannel::Sms],
        title:          'Reminder',
        body:           'Your appointment is tomorrow.',
        organizationId: 'org-1',
        branchId:       'branch-1',
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
        notifiableId:   'user-1',
        channels:       [NotificationChannel::Email],
        title:          'Test',
        body:           'Test body.',
        organizationId: 'org-1',
    );

    app(NotificationServiceInterface::class)->send($message);

    Queue::assertPushed(SendNotificationJob::class);
});

it('sendMany dispatches for each message', function (): void {
    $messages = [
        new NotificationMessageDTO(
            type: 'test', notifiableType: 'User', notifiableId: 'u1',
            channels: [NotificationChannel::Email], title: 'T1', body: 'B1', organizationId: 'org-1',
        ),
        new NotificationMessageDTO(
            type: 'test', notifiableType: 'User', notifiableId: 'u2',
            channels: [NotificationChannel::Sms], title: 'T2', body: 'B2', organizationId: 'org-1',
        ),
    ];

    app(NotificationServiceInterface::class)->sendMany($messages);

    expect(Notification::count())->toBe(2);
});

it('markAsRead returns false for non-in-app channel', function (): void {
    $notification = Notification::create([
        'id'              => Notification::newUuid(),
        'organization_id' => 'org-1',
        'notifiable_type' => 'User',
        'notifiable_id'   => 'user-1',
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
        'organization_id' => 'org-1',
        'notifiable_type' => 'User',
        'notifiable_id'   => 'user-1',
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
        'organization_id' => 'org-1',
        'notifiable_type' => 'User',
        'notifiable_id'   => 'user-1',
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
    $result = app(NotificationServiceInterface::class)->markAsRead('nonexistent-id');
    expect($result)->toBeFalse();
});

it('Notification model extends BaseModel with HasUuid, HasAudit, SoftDeletes', function (): void {
    $traits = class_uses(Notification::class);

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
