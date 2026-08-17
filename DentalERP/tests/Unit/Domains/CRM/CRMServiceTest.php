<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\CRM\DTO\CreateCRMDTO;
use App\Domains\CRM\DTO\UpdateCRMDTO;
use App\Domains\CRM\Interfaces\CRMServiceInterface;
use App\Domains\CRM\Models\CRM;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId     = (string) Str::orderedUuid();
    $this->branchId  = (string) Str::orderedUuid();
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-CRM-01', 'company_name' => 'CRM Test Org',
        'email' => 'crm@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-CRM-01',
        'branch_name' => 'CRM Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-CRM-01',
        'full_name' => 'CRM Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(CRMServiceInterface::class);
});

it('creates CRM contact from DTO and returns CRM model', function (): void {
    $dto = new CreateCRMDTO(
        contactType: 'inquiry',
        organizationId: $this->orgId,
        patientId: $this->patientId,
        channel: 'phone',
        subject: 'Test inquiry',
        message: null,
        followUpDate: null,
        resolution: null,
    );

    $crm = $this->service->create($dto);

    expect($crm)->toBeInstanceOf(CRM::class);
    expect($crm->contact_type)->toBe('inquiry');
    expect($crm->organization_id)->toBe($this->orgId);
    expect($crm->status)->toBe('new');
});

it('throws BusinessException when status transition is invalid (closed → in_progress)', function (): void {
    $created = $this->service->create(new CreateCRMDTO(
        contactType: 'complaint', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    $this->service->update($created->id, new UpdateCRMDTO(
        status: 'closed',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateCRMDTO(
        status: 'in_progress',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds CRM contact by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateCRMDTO(
        contactType: 'inquiry', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->contact_type)->toBe('inquiry');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateCRMDTO(
        contactType: 'inquiry', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates CRM contacts scoped to organization', function (): void {
    $this->service->create(new CreateCRMDTO(
        contactType: 'inquiry', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));
    $this->service->create(new CreateCRMDTO(
        contactType: 'complaint', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates CRM contact from DTO', function (): void {
    $created = $this->service->create(new CreateCRMDTO(
        contactType: 'inquiry', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    $updated = $this->service->update($created->id, new UpdateCRMDTO(
        status: 'in_progress',
        subject: 'Updated subject',
        message: 'Updated message',
    ), $this->orgId);

    expect($updated->status)->toBe('in_progress');
    expect($updated->subject)->toBe('Updated subject');
    expect($updated->message)->toBe('Updated message');
});

it('soft-deletes CRM contact', function (): void {
    $created = $this->service->create(new CreateCRMDTO(
        contactType: 'inquiry', organizationId: $this->orgId,
        patientId: null, channel: null, subject: null,
        message: null, followUpDate: null, resolution: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(CRM::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});