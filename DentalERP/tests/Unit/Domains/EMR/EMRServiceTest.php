<?php

declare(strict_types=1);

use App\Core\Exceptions\NotFoundException;
use App\Domains\EMR\DTO\CreateEMRDTO;
use App\Domains\EMR\DTO\UpdateEMRDTO;
use App\Domains\EMR\Interfaces\EMRServiceInterface;
use App\Domains\EMR\Models\EMR;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-EMR-01', 'company_name' => 'EMR Test Org',
        'email' => 'emr@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-EMR-01',
        'branch_name' => 'EMR Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-EMR-01',
        'full_name' => 'EMR Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(EMRServiceInterface::class);
});

it('creates emr from DTO', function (): void {
    $dto = new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: 'Toothache', diagnosis: 'Caries',
        treatmentNotes: null, vitalSigns: null, status: 'open',
    );

    $emr = $this->service->create($dto);
    expect($emr)->toBeInstanceOf(EMR::class);
    expect($emr->chief_complaint)->toBe('Toothache');
    expect($emr->status)->toBe('open');
});

it('handles duplicate emr creation', function (): void {
    $dto = new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    );

    $first = $this->service->create($dto);
    $second = $this->service->create($dto);
    expect($first->id)->not->toBe($second->id);
});

it('finds emr by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    ));
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $created = $this->service->create(new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    ));
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('updates emr from DTO', function (): void {
    $created = $this->service->create(new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    ));
    $updated = $this->service->update($created->id, new UpdateEMRDTO(
        diagnosis: 'Updated diagnosis', treatmentNotes: 'Some notes',
    ), $this->orgId);
    expect($updated->diagnosis)->toBe('Updated diagnosis');
    expect($updated->treatment_notes)->toBe('Some notes');
});

it('soft-deletes emr', function (): void {
    $created = $this->service->create(new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    ));
    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();
    expect(EMR::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips status', function (): void {
    $created = $this->service->create(new CreateEMRDTO(
        organizationId: $this->orgId, patientId: $this->patientId, doctorId: null,
        appointmentId: null, chiefComplaint: null, diagnosis: null,
        treatmentNotes: null, vitalSigns: null, status: 'open',
    ));
    expect($created->status)->toBe('open');
    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->status)->toBe('completed');
});