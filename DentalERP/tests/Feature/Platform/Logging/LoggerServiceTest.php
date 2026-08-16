<?php

declare(strict_types=1);

use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Logging\Models\SystemLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Log::spy();
});

it('log writes to file for all severity levels', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->info('[TestService::run] Info message.', ['key' => 'value']);
    $service->warning('[TestService::run] Warning message.');

    Log::shouldHaveReceived('info');
    Log::shouldHaveReceived('warning');
});

it('log persists to database for warning and above', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->warning('[TestService::run] Warning event.', ['org' => 'org-1']);
    $service->error('[TestService::run] Error event.', ['exception' => 'RuntimeException']);

    $warningRecord = SystemLog::where('level', 'warning')->first();
    $errorRecord = SystemLog::where('level', 'error')->first();

    expect($warningRecord)->not->toBeNull();
    expect($warningRecord->message)->toBe('[TestService::run] Warning event.');
    expect($errorRecord)->not->toBeNull();
    expect($errorRecord->message)->toBe('[TestService::run] Error event.');
});

it('log does not persist to database for notice, info, debug', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->notice('[Test::run] Notice.');
    $service->info('[Test::run] Info.');
    $service->debug('[Test::run] Debug.');

    expect(SystemLog::whereIn('level', ['notice', 'info', 'debug'])->count())->toBe(0);
});

it('log persists emergency, alert, critical to database', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->emergency('[Test::run] System down!');
    $service->alert('[Test::run] Database unreachable.');
    $service->critical('[Test::run] Critical failure.');

    expect(SystemLog::where('level', 'emergency')->first())->not->toBeNull();
    expect(SystemLog::where('level', 'alert')->first())->not->toBeNull();
    expect(SystemLog::where('level', 'critical')->first())->not->toBeNull();
});

it('debug is suppressed in production environment', function (): void {
    config(['app.env' => 'production']);
    $service = app(LoggerServiceInterface::class);

    $service->debug('[Test::run] Debug info.');

    Log::shouldNotHaveReceived('debug');
    expect(SystemLog::where('level', 'debug')->count())->toBe(0);
});

it('debug writes to file in non-production', function (): void {
    config(['app.env' => 'local']);
    $service = app(LoggerServiceInterface::class);

    $service->debug('[Test::run] Debug info.');

    Log::shouldHaveReceived('debug');
});

it('extracts channel from [Module::action] message format', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->error('[PatientService::create] Failed.');

    $record = SystemLog::first();
    expect($record->channel)->toBe('PatientService');
});

it('falls back to system channel when message has no module prefix', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->error('Plain message without module prefix.');

    $record = SystemLog::first();
    expect($record->channel)->toBe('system');
});

it('stores tenant context when provided', function (): void {
    $service = app(LoggerServiceInterface::class);
    $orgId    = (string) Str::orderedUuid();
    $branchId = (string) Str::orderedUuid();
    $userId   = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $orgId, 'company_code' => 'ORG-LOG-01', 'company_name' => 'Logging Test Org',
        'email' => 'log@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $branchId, 'organization_id' => $orgId, 'branch_code' => 'BRC-LOG-01',
        'branch_name' => 'Logging Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $userId, 'organization_id' => $orgId, 'branch_id' => $branchId,
        'name' => 'Logging Test User', 'email' => 'log-user@test.com', 'username' => 'loguser',
        'employee_code' => 'EMP-LOG-01',
        'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $service->warning('[AuthService::login] Login attempt.', [
        'organization_id' => $orgId,
        'branch_id'       => $branchId,
        'user_id'         => $userId,
    ]);

    $record = SystemLog::first();
    expect($record->organization_id)->toBe($orgId);
    expect($record->branch_id)->toBe($branchId);
    expect($record->user_id)->toBe($userId);
});

it('allows null tenant context for non-authenticated contexts', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->error('[QueueWorker::process] Failed.', []);

    $record = SystemLog::first();
    expect($record->organization_id)->toBeNull();
    expect($record->branch_id)->toBeNull();
    expect($record->user_id)->toBeNull();
});

it('stores exception context when provided', function (): void {
    $service = app(LoggerServiceInterface::class);

    $service->error('[ApiService::call] Exception occurred.', [
        'exception' => 'RuntimeException',
        'file'      => '/var/www/app/Service.php',
        'line'      => 42,
        'trace'     => "#0 ...\n#1 ...",
        'ip_address' => '10.0.0.1',
    ]);

    $record = SystemLog::first();
    expect($record->exception_class)->toBe('RuntimeException');
    expect($record->file)->toBe('/var/www/app/Service.php');
    expect($record->line)->toBe(42);
    expect($record->trace)->toContain('#0');
    expect($record->ip_address)->toBe('10.0.0.1');
});

it('SystemLog model uses HasUuid and not SoftDeletes', function (): void {
    $traits = class_uses(SystemLog::class);

    expect($traits)->toHaveKey('App\Core\Traits\HasUuid');
    expect($traits)->not->toHaveKey('Illuminate\Database\Eloquent\SoftDeletes');
});

it('SystemLog model has timestamps disabled', function (): void {
    $model = new SystemLog();

    expect($model->timestamps)->toBeFalse();
    expect($model->getTable())->toBe('system_logs');
});

it('LoggerService implements LoggerServiceInterface', function (): void {
    $service = app(LoggerServiceInterface::class);

    expect($service)->toBeInstanceOf(LoggerServiceInterface::class);
});