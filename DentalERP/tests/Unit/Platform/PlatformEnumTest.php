<?php

declare(strict_types=1);

use App\Platform\Audit\Enums\AuditAction;
use App\Platform\FileStorage\Enums\StorageFolder;
use App\Platform\FileStorage\Enums\StorageDriver;
use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Notification\Enums\NotificationStatus;
use App\Platform\Notification\Enums\NotificationChannel;

it('AuditAction has 11 cases', function (): void {
    expect(AuditAction::cases())->toHaveCount(11);
});

it('LogLevel has 8 cases', function (): void {
    expect(LogLevel::cases())->toHaveCount(8);
});

it('StorageFolder has 7 cases', function (): void {
    expect(StorageFolder::cases())->toHaveCount(7);
});

it('StorageDriver has 2 cases', function (): void {
    expect(StorageDriver::cases())->toHaveCount(2);
});

it('NotificationStatus has 4 cases', function (): void {
    expect(NotificationStatus::cases())->toHaveCount(4);
});

it('NotificationChannel has 5 cases', function (): void {
    expect(NotificationChannel::cases())->toHaveCount(5);
});

it('LogLevel shouldPersist returns true for warning and above', function (): void {
    expect(LogLevel::Emergency->shouldPersist())->toBeTrue();
    expect(LogLevel::Alert->shouldPersist())->toBeTrue();
    expect(LogLevel::Critical->shouldPersist())->toBeTrue();
    expect(LogLevel::Error->shouldPersist())->toBeTrue();
    expect(LogLevel::Warning->shouldPersist())->toBeTrue();
    expect(LogLevel::Notice->shouldPersist())->toBeFalse();
    expect(LogLevel::Info->shouldPersist())->toBeFalse();
    expect(LogLevel::Debug->shouldPersist())->toBeFalse();
});

it('AuditAction isMutation classifies correctly', function (): void {
    expect(AuditAction::Create->isMutation())->toBeTrue();
    expect(AuditAction::Update->isMutation())->toBeTrue();
    expect(AuditAction::Delete->isMutation())->toBeTrue();
    expect(AuditAction::Restore->isMutation())->toBeTrue();
    expect(AuditAction::Login->isMutation())->toBeFalse();
    expect(AuditAction::Export->isMutation())->toBeFalse();
});

it('StorageFolder maxSizeBytes returns correct limits', function (): void {
    expect(StorageFolder::Radiology->maxSizeBytes())->toBe(100 * 1024 * 1024);
    expect(StorageFolder::Lab->maxSizeBytes())->toBe(20 * 1024 * 1024);
    expect(StorageFolder::Patient->maxSizeBytes())->toBe(10 * 1024 * 1024);
    expect(StorageFolder::Organization->maxSizeBytes())->toBe(5 * 1024 * 1024);
});
