<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Patient\DTO\CreatePatientDTO;
use App\Domains\Patient\DTO\UpdatePatientDTO;
use App\Domains\Patient\Interfaces\PatientServiceInterface;
use App\Domains\Patient\Models\Patient;
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
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-PAT-01', 'company_name' => 'Patient Test Org',
        'email' => 'pat@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-PAT-01',
        'branch_name' => 'Patient Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(PatientServiceInterface::class);
});

it('creates patient from DTO', function (): void {
    $dto = new CreatePatientDTO(
        patientCode: 'PAT-001', fullName: 'John Doe', organizationId: $this->orgId,
        branchId: $this->branchId, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    );

    $patient = $this->service->create($dto);
    expect($patient)->toBeInstanceOf(Patient::class);
    expect($patient->patient_code)->toBe('PAT-001');
    expect($patient->full_name)->toBe('John Doe');
});

it('throws BusinessException on duplicate patient_code', function (): void {
    $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-002', fullName: 'First', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));

    expect(fn () => $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-002', fullName: 'Second', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    )))->toThrow(BusinessException::class);
});

it('finds patient by id scoped to organization', function (): void {
    $created = $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-003', fullName: 'Jane Doe', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $created = $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-004', fullName: 'Other', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('updates patient from DTO', function (): void {
    $created = $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-005', fullName: 'Original', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));
    $updated = $this->service->update($created->id, new UpdatePatientDTO(
        fullName: 'Updated Name', phone: '081234567890',
    ), $this->orgId);
    expect($updated->full_name)->toBe('Updated Name');
    expect($updated->phone)->toBe('081234567890');
});

it('soft-deletes patient', function (): void {
    $created = $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-006', fullName: 'Deletable', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));
    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();
    expect(Patient::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips is_active', function (): void {
    $created = $this->service->create(new CreatePatientDTO(
        patientCode: 'PAT-007', fullName: 'Toggle', organizationId: $this->orgId,
        branchId: null, birthDate: null, gender: null, bloodType: null,
        religion: null, maritalStatus: null, nationalityId: null, patientTypeId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
    ));
    expect($created->is_active)->toBeTrue();
    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->is_active)->toBeFalse();
});