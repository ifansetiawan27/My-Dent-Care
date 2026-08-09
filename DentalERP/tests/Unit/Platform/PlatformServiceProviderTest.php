<?php

declare(strict_types=1);

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\FileStorage\Contracts\FileStorageServiceInterface;
use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Notification\Contracts\NotificationServiceInterface;

it('resolves AuditServiceInterface from container', function (): void {
    $instance = app(AuditServiceInterface::class);
    expect($instance)->toBeInstanceOf(AuditServiceInterface::class);
});

it('resolves FileStorageServiceInterface from container', function (): void {
    $instance = app(FileStorageServiceInterface::class);
    expect($instance)->toBeInstanceOf(FileStorageServiceInterface::class);
});

it('resolves LoggerServiceInterface from container', function (): void {
    $instance = app(LoggerServiceInterface::class);
    expect($instance)->toBeInstanceOf(LoggerServiceInterface::class);
});

it('resolves NotificationServiceInterface from container', function (): void {
    $instance = app(NotificationServiceInterface::class);
    expect($instance)->toBeInstanceOf(NotificationServiceInterface::class);
});

it('binds each service with independent instances', function (): void {
    $a = app(AuditServiceInterface::class);
    $b = app(AuditServiceInterface::class);
    expect($a)->toBeInstanceOf(AuditServiceInterface::class);
    expect($b)->toBeInstanceOf(AuditServiceInterface::class);
    expect($a)->not->toBe($b);
});
