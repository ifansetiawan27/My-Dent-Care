<?php

declare(strict_types=1);

use App\Platform\Logging\Enums\LogLevel;

it('severity returns correct numeric values per RFC 5424', function (): void {
    expect(LogLevel::Emergency->severity())->toBe(0);
    expect(LogLevel::Alert->severity())->toBe(1);
    expect(LogLevel::Critical->severity())->toBe(2);
    expect(LogLevel::Error->severity())->toBe(3);
    expect(LogLevel::Warning->severity())->toBe(4);
    expect(LogLevel::Notice->severity())->toBe(5);
    expect(LogLevel::Info->severity())->toBe(6);
    expect(LogLevel::Debug->severity())->toBe(7);
});

it('shouldPersist returns true for warning and above', function (): void {
    expect(LogLevel::Emergency->shouldPersist())->toBeTrue();
    expect(LogLevel::Alert->shouldPersist())->toBeTrue();
    expect(LogLevel::Critical->shouldPersist())->toBeTrue();
    expect(LogLevel::Error->shouldPersist())->toBeTrue();
    expect(LogLevel::Warning->shouldPersist())->toBeTrue();
    expect(LogLevel::Notice->shouldPersist())->toBeFalse();
    expect(LogLevel::Info->shouldPersist())->toBeFalse();
    expect(LogLevel::Debug->shouldPersist())->toBeFalse();
});

it('shouldForwardExternal returns true for error and above', function (): void {
    expect(LogLevel::Emergency->shouldForwardExternal())->toBeTrue();
    expect(LogLevel::Alert->shouldForwardExternal())->toBeTrue();
    expect(LogLevel::Critical->shouldForwardExternal())->toBeTrue();
    expect(LogLevel::Error->shouldForwardExternal())->toBeTrue();
    expect(LogLevel::Warning->shouldForwardExternal())->toBeFalse();
    expect(LogLevel::Notice->shouldForwardExternal())->toBeFalse();
    expect(LogLevel::Info->shouldForwardExternal())->toBeFalse();
    expect(LogLevel::Debug->shouldForwardExternal())->toBeFalse();
});

it('values returns all 8 level strings', function (): void {
    $values = LogLevel::values();
    expect($values)->toHaveCount(8);
    expect($values)->toContain('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug');
});
