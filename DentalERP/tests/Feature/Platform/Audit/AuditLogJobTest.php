<?php

declare(strict_types=1);

use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Audit\Jobs\AuditLogJob;
use App\Platform\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('AuditLogJob persists audit record to database', function (): void {
    $entry = new AuditEntryDTO(
        action:         AuditAction::Create,
        module:         'patient',
        userId:         '01927abc-def0-7000-8000-000000000001',
        organizationId: '01927abc-def0-7000-8000-000000000010',
        branchId:       '01927abc-def0-7000-8000-000000000020',
        auditableType:  'App\\Domains\\Patient\\Models\\Patient',
        auditableId:    '01927abc-def0-7000-8000-000000000030',
        oldValue:       [],
        newValue:       ['name' => 'John Doe', 'email' => 'john@example.com'],
        ipAddress:      '192.168.1.1',
        userAgent:      'Mozilla/5.0',
        device:         'desktop',
    );

    $job = new AuditLogJob($entry);
    $job->handle();

    $record = AuditLog::first();

    expect($record)->not->toBeNull();
    expect($record->user_id)->toBe('01927abc-def0-7000-8000-000000000001');
    expect($record->organization_id)->toBe('01927abc-def0-7000-8000-000000000010');
    expect($record->branch_id)->toBe('01927abc-def0-7000-8000-000000000020');
    expect($record->module)->toBe('patient');
    expect($record->action)->toBe('create');
    expect($record->auditable_type)->toBe('App\\Domains\\Patient\\Models\\Patient');
    expect($record->auditable_id)->toBe('01927abc-def0-7000-8000-000000000030');
    expect($record->old_value)->toBe([]);
    expect($record->new_value)->toBe(['name' => 'John Doe', 'email' => 'john@example.com']);
    expect($record->ip_address)->toBe('192.168.1.1');
    expect($record->user_agent)->toBe('Mozilla/5.0');
    expect($record->device)->toBe('desktop');
    expect($record->created_at)->not->toBeNull();
});

it('AuditLogJob persists login event without auditable context', function (): void {
    $entry = new AuditEntryDTO(
        action:         AuditAction::Login,
        module:         'auth',
        userId:         '01927abc-def0-7000-8000-000000000001',
        organizationId: '01927abc-def0-7000-8000-000000000010',
    );

    $job = new AuditLogJob($entry);
    $job->handle();

    $record = AuditLog::first();

    expect($record->action)->toBe('login');
    expect($record->auditable_type)->toBeNull();
    expect($record->auditable_id)->toBeNull();
});

it('AuditLogJob persists delete event with empty new_value and populated old_value', function (): void {
    $entry = new AuditEntryDTO(
        action:         AuditAction::Delete,
        module:         'patient',
        organizationId: '01927abc-def0-7000-8000-000000000010',
        auditableType:  'Patient',
        auditableId:    '01927abc-def0-7000-8000-000000000030',
        oldValue:       ['name' => 'Deleted Patient', 'status' => 'active'],
        newValue:       [],
    );

    $job = new AuditLogJob($entry);
    $job->handle();

    $record = AuditLog::first();

    expect($record->action)->toBe('delete');
    expect($record->old_value)->toBe(['name' => 'Deleted Patient', 'status' => 'active']);
    expect($record->new_value)->toBe([]);
});

it('AuditLog records are identifiable by auditable_type and auditable_id', function (): void {
    $entry1 = new AuditEntryDTO(
        action: AuditAction::Create, module: 'test',
        auditableType: 'Patient', auditableId: 'patient-1',
    );
    $entry2 = new AuditEntryDTO(
        action: AuditAction::Update, module: 'test',
        auditableType: 'Patient', auditableId: 'patient-1',
    );

    (new AuditLogJob($entry1))->handle();
    (new AuditLogJob($entry2))->handle();

    $records = AuditLog::where('auditable_type', 'Patient')
        ->where('auditable_id', 'patient-1')
        ->get();

    expect($records)->toHaveCount(2);
});
