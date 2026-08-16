<?php

declare(strict_types=1);

use App\Platform\Audit\DTO\AuditEntryDTO;
use App\Platform\Audit\Enums\AuditAction;
use App\Platform\Audit\Jobs\AuditLogJob;
use App\Platform\Audit\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->orgId   = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $this->userId   = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-AUD-01', 'company_name' => 'Audit Test Org',
        'email' => 'audit@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-AUD-01',
        'branch_name' => 'Audit Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Audit Test User', 'email' => 'audit-user@test.com', 'username' => 'audituser',
        'employee_code' => 'EMP-AUD-01',
        'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

it('AuditLogJob persists audit record to database', function (): void {
    $entry = new AuditEntryDTO(
        action:         AuditAction::Create,
        module:         'patient',
        userId:         $this->userId,
        organizationId: $this->orgId,
        branchId:       $this->branchId,
        auditableType:  'App\\Domains\\Patient\\Models\\Patient',
        auditableId:    $this->userId,
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
    expect($record->user_id)->toBe($this->userId);
    expect($record->organization_id)->toBe($this->orgId);
    expect($record->branch_id)->toBe($this->branchId);
    expect($record->module)->toBe('patient');
    expect($record->action)->toBe('create');
    expect($record->auditable_type)->toBe('App\\Domains\\Patient\\Models\\Patient');
    expect($record->auditable_id)->toBe($this->userId);
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
        userId:         $this->userId,
        organizationId: $this->orgId,
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
        organizationId: $this->orgId,
        auditableType:  'Patient',
        auditableId:    $this->userId,
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
    $targetId = (string) Str::orderedUuid();

    $entry1 = new AuditEntryDTO(
        action: AuditAction::Create, module: 'test',
        organizationId: $this->orgId,
        auditableType: 'Patient', auditableId: $targetId,
    );
    $entry2 = new AuditEntryDTO(
        action: AuditAction::Update, module: 'test',
        organizationId: $this->orgId,
        auditableType: 'Patient', auditableId: $targetId,
    );

    (new AuditLogJob($entry1))->handle();
    (new AuditLogJob($entry2))->handle();

    $records = AuditLog::where('auditable_type', 'Patient')
        ->where('auditable_id', $targetId)
        ->get();

    expect($records)->toHaveCount(2);
});
