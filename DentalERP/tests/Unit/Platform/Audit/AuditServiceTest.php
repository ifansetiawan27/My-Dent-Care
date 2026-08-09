<?php

declare(strict_types=1);

use App\Platform\Audit\Contracts\AuditServiceInterface;
use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Audit\Jobs\AuditLogJob;
use App\Platform\Audit\Models\AuditLog;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

it('record dispatches AuditLogJob to queue', function (): void {
    $entry = new AuditEntryDTO(
        action:        AuditAction::Create,
        module:        'patient',
        userId:        '01927abc-def0-7000-8000-000000000001',
        organizationId: '01927abc-def0-7000-8000-000000000010',
        branchId:       '01927abc-def0-7000-8000-000000000020',
        auditableType: 'App\\Domains\\Patient\\Models\\Patient',
        auditableId:   '01927abc-def0-7000-8000-000000000030',
        oldValue:      [],
        newValue:      ['name' => 'John Doe'],
    );

    app(AuditServiceInterface::class)->record($entry);

    Queue::assertPushed(AuditLogJob::class);
});

it('log builds AuditEntryDTO and dispatches to queue', function (): void {
    app(AuditServiceInterface::class)->log(
        action:        AuditAction::Update,
        module:        'organization',
        auditableType: 'App\\Domains\\Organization\\Models\\Organization',
        auditableId:   '01927abc-def0-7000-8000-000000000040',
        oldValue:      ['name' => 'Old Clinic'],
        newValue:      ['name' => 'New Clinic'],
    );

    Queue::assertPushed(AuditLogJob::class);
});

it('record never throws exception', function (): void {
    $entry = new AuditEntryDTO(
        action:  AuditAction::Create,
        module:  'test',
    );

    $result = null;
    try {
        app(AuditServiceInterface::class)->record($entry);
        $result = 'ok';
    } catch (\Throwable) {
        $result = 'exception';
    }

    expect($result)->toBe('ok');
});

it('old_value defaults to empty array on create', function (): void {
    $entry = new AuditEntryDTO(
        action: AuditAction::Create,
        module: 'test',
    );

    expect($entry->oldValue)->toBe([]);
});

it('new_value defaults to empty array on delete', function (): void {
    $entry = new AuditEntryDTO(
        action: AuditAction::Delete,
        module: 'test',
    );

    expect($entry->newValue)->toBe([]);
});

it('login and logout events have nullable auditable context', function (): void {
    $entry = new AuditEntryDTO(
        action: AuditAction::Login,
        module: 'auth',
    );

    expect($entry->auditableType)->toBeNull();
    expect($entry->auditableId)->toBeNull();
});

it('AuditLog model is immutable — no updated_at column', function (): void {
    $model = new AuditLog();

    expect($model->timestamps)->toBeFalse();
    expect($model->getTable())->toBe('audit_logs');
});

it('AuditLog does not use SoftDeletes trait', function (): void {
    $traits = class_uses(AuditLog::class);

    expect($traits)->not->toHaveKey('Illuminate\Database\Eloquent\SoftDeletes');
});

it('AuditLog uses HasUuid trait', function (): void {
    $traits = class_uses(AuditLog::class);

    expect($traits)->toHaveKey('App\Core\Traits\HasUuid');
});

it('AuditEntryDTO toArray serializes correctly', function (): void {
    $entry = new AuditEntryDTO(
        action:         AuditAction::Update,
        module:         'patient',
        userId:         'user-1',
        organizationId: 'org-1',
        branchId:       'branch-1',
        auditableType:  'Patient',
        auditableId:    'patient-1',
        oldValue:       ['name' => 'Old'],
        newValue:       ['name' => 'New'],
        ipAddress:      '127.0.0.1',
        userAgent:      'PHPUnit',
        device:         'desktop',
    );

    $array = $entry->toArray();

    expect($array['action'])->toBe('update');
    expect($array['module'])->toBe('patient');
    expect($array['user_id'])->toBe('user-1');
    expect($array['organization_id'])->toBe('org-1');
    expect($array['branch_id'])->toBe('branch-1');
    expect($array['auditable_type'])->toBe('Patient');
    expect($array['auditable_id'])->toBe('patient-1');
    expect($array['old_value'])->toBe(['name' => 'Old']);
    expect($array['new_value'])->toBe(['name' => 'New']);
    expect($array['ip_address'])->toBe('127.0.0.1');
    expect($array['user_agent'])->toBe('PHPUnit');
    expect($array['device'])->toBe('desktop');
});

it('AuditEntryDTO is readonly — immutable value object', function (): void {
    $reflection = new ReflectionClass(AuditEntryDTO::class);

    expect($reflection->isReadOnly())->toBeTrue();
});
