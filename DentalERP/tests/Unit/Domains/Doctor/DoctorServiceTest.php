<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Doctor\DTO\CreateDoctorDTO;
use App\Domains\Doctor\DTO\UpdateDoctorDTO;
use App\Domains\Doctor\Interfaces\DoctorServiceInterface;
use App\Domains\Doctor\Models\Doctor;
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
        'id' => $this->orgId, 'company_code' => 'ORG-DOC-01', 'company_name' => 'Doctor Test Org',
        'email' => 'doc@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-DOC-01',
        'branch_name' => 'Doctor Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(DoctorServiceInterface::class);
});

it('creates doctor from DTO', function (): void {
    $dto = new CreateDoctorDTO(
        doctorCode: 'DOC-001', fullName: 'Dr. Smith', organizationId: $this->orgId,
        branchId: $this->branchId, specialtyId: null, licenseNumber: null,
        consultationFee: null, gender: null, religion: null, maritalStatus: null,
        nationalityId: null, phone: null, email: null, address: null,
        districtId: null, villageId: null, hireDate: null, resignationDate: null,
    );

    $doctor = $this->service->create($dto);
    expect($doctor)->toBeInstanceOf(Doctor::class);
    expect($doctor->doctor_code)->toBe('DOC-001');
    expect($doctor->full_name)->toBe('Dr. Smith');
});

it('throws BusinessException on duplicate doctor_code', function (): void {
    $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-002', fullName: 'First', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));

    expect(fn () => $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-002', fullName: 'Second', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    )))->toThrow(BusinessException::class);
});

it('finds doctor by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-003', fullName: 'Dr. Jones', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $created = $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-004', fullName: 'Other', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('updates doctor from DTO', function (): void {
    $created = $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-005', fullName: 'Original', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));
    $updated = $this->service->update($created->id, new UpdateDoctorDTO(
        fullName: 'Updated Name', licenseNumber: 'LIC-123',
    ), $this->orgId);
    expect($updated->full_name)->toBe('Updated Name');
    expect($updated->license_number)->toBe('LIC-123');
});

it('soft-deletes doctor', function (): void {
    $created = $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-006', fullName: 'Deletable', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));
    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();
    expect(Doctor::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips is_active', function (): void {
    $created = $this->service->create(new CreateDoctorDTO(
        doctorCode: 'DOC-007', fullName: 'Toggle', organizationId: $this->orgId,
        branchId: null, specialtyId: null, licenseNumber: null, consultationFee: null,
        gender: null, religion: null, maritalStatus: null, nationalityId: null,
        phone: null, email: null, address: null, districtId: null, villageId: null,
        hireDate: null, resignationDate: null,
    ));
    expect($created->is_active)->toBeTrue();
    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->is_active)->toBeFalse();
});