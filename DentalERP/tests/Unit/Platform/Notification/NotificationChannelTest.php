<?php

declare(strict_types=1);

use App\Platform\Notification\Channels\EmailChannel;
use App\Platform\Notification\Channels\InAppChannel;
use App\Platform\Notification\Channels\PushChannel;
use App\Platform\Notification\Channels\SmsChannel;
use App\Platform\Notification\Channels\WhatsAppChannel;
use App\Platform\Notification\Contracts\NotificationChannelInterface;
use App\Platform\Notification\Contracts\NotificationServiceInterface;
use App\Platform\Notification\DTO\NotificationMessageDTO;
use App\Platform\Notification\Enums\NotificationChannel;
use App\Platform\Notification\Enums\NotificationStatus;

it('EmailChannel implements NotificationChannelInterface', function (): void {
    $channel = new EmailChannel();
    expect($channel)->toBeInstanceOf(NotificationChannelInterface::class);
    expect($channel->channel())->toBe(NotificationChannel::Email);
    expect($channel->isAvailableFor('org-1'))->toBeTrue();
});

it('WhatsAppChannel implements NotificationChannelInterface', function (): void {
    $channel = new WhatsAppChannel();
    expect($channel)->toBeInstanceOf(NotificationChannelInterface::class);
    expect($channel->channel())->toBe(NotificationChannel::WhatsApp);
    expect($channel->isAvailableFor('org-1'))->toBeFalse();
});

it('SmsChannel implements NotificationChannelInterface', function (): void {
    $channel = new SmsChannel();
    expect($channel)->toBeInstanceOf(NotificationChannelInterface::class);
    expect($channel->channel())->toBe(NotificationChannel::Sms);
});

it('PushChannel implements NotificationChannelInterface', function (): void {
    $channel = new PushChannel();
    expect($channel)->toBeInstanceOf(NotificationChannelInterface::class);
    expect($channel->channel())->toBe(NotificationChannel::Push);
});

it('InAppChannel implements NotificationChannelInterface', function (): void {
    $channel = new InAppChannel();
    expect($channel)->toBeInstanceOf(NotificationChannelInterface::class);
    expect($channel->channel())->toBe(NotificationChannel::InApp);
    expect($channel->isAvailableFor('org-1'))->toBeTrue();
});

it('all channel drivers deliver reports true for stub implementations', function (): void {
    $message = new NotificationMessageDTO(
        type: 'test', notifiableType: 'U', notifiableId: '1',
        channels: [NotificationChannel::Email], title: 'T', body: 'B',
    );

    expect((new EmailChannel())->deliver($message))->toBeTrue();
    expect((new InAppChannel())->deliver($message))->toBeTrue();
});

it('NotificationService implements interface', function (): void {
    expect(app(NotificationServiceInterface::class))
        ->toBeInstanceOf(NotificationServiceInterface::class);
});

it('NotificationStatus has correct values and labels', function (): void {
    expect(NotificationStatus::Pending->value)->toBe('pending');
    expect(NotificationStatus::Sent->value)->toBe('sent');
    expect(NotificationStatus::Failed->value)->toBe('failed');
    expect(NotificationStatus::Read->value)->toBe('read');

    expect(NotificationStatus::Pending->isFinal())->toBeFalse();
    expect(NotificationStatus::Sent->isFinal())->toBeFalse();
    expect(NotificationStatus::Failed->isFinal())->toBeTrue();
    expect(NotificationStatus::Read->isFinal())->toBeTrue();
    expect(NotificationStatus::values())->toHaveCount(4);
});

it('NotificationChannel has correct values and labels', function (): void {
    expect(NotificationChannel::Email->value)->toBe('email');
    expect(NotificationChannel::WhatsApp->value)->toBe('whatsapp');
    expect(NotificationChannel::Sms->value)->toBe('sms');
    expect(NotificationChannel::Push->value)->toBe('push');
    expect(NotificationChannel::InApp->value)->toBe('in_app');

    expect(NotificationChannel::WhatsApp->usesIntegrationHub())->toBeTrue();
    expect(NotificationChannel::Sms->usesIntegrationHub())->toBeTrue();
    expect(NotificationChannel::Push->usesIntegrationHub())->toBeTrue();
    expect(NotificationChannel::Email->usesIntegrationHub())->toBeFalse();
    expect(NotificationChannel::InApp->usesIntegrationHub())->toBeFalse();

    expect(NotificationChannel::values())->toHaveCount(5);
});

it('NotificationMessageDTO is readonly', function (): void {
    $reflection = new ReflectionClass(NotificationMessageDTO::class);

    expect($reflection->isReadOnly())->toBeTrue();
});
