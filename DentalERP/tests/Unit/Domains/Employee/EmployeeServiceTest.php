<?php

declare(strict_types=1);

use App\Domains\Employee\DTO\CreateEmployeeDTO;
use App\Domains\Employee\DTO\UpdateEmployeeDTO;
use App\Domains\Employee\Interfaces\EmployeeRepositoryInterface;
use App\Domains\Employee\Interfaces\EmployeeServiceInterface;
use App\Domains\Employee\Models\Employee;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Auth::shouldReceive('check')->andReturn(false);
    Auth::shouldReceive('id')->andReturn(null);
    Auth::shouldReceive('user')->andReturn(null);

    $this->orgId    = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-EMP-01', 'company_name' => 'Employee Test Org',
        'email' => 'emp@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-EMP-01',
        'branch_name' => 'Employee Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(EmployeeServiceInterface::class);
});

it('creates employee from DTO and returns Employee model', function (): void {
    $dto = new CreateEmployeeDTO(
        employeeCode:     'EMP-001',
        fullName:         'John Doe',
        organizationId:   $this->orgId,
        branchId:         $this->branchId,
        employmentStatus: 'active',
        hireDate:         '2026-01-01',
        resignationDate:  null,
        position:         'Dentist',
        gender:           'male',
        religion:         null,
        maritalStatus:    null,
        nationalityId:    null,
        phone:            null,
        email:            null,
        address:          null,
        districtId:       null,
        villageId:        null,
    );

    $employee = $this->service->create($dto);

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->employee_code)->toBe('EMP-001');
    expect($employee->full_name)->toBe('John Doe');
    expect($employee->organization_id)->toBe($this->orgId);
    expect($employee->branch_id)->toBe($this->branchId);
    expect($employee->position)->toBe('Dentist');
});

it('throws BusinessException when employee_code already exists', function (): void {
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-001', fullName: 'First', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    expect(fn () => $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-001', fullName: 'Second', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    )))->toThrow(BusinessException::class, 'Employee code already taken.');
});

it('finds employee by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-002', fullName: 'Jane', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->full_name)->toBe('Jane');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-003', fullName: 'Other', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates employees scoped to organization', function (): void {
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-004', fullName: 'Alice', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-005', fullName: 'Bob', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('paginates with search filter', function (): void {
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-010', fullName: 'Searchable', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-011', fullName: 'Hidden', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId, 'search' => 'Search']);

    expect($result->total())->toBe(1);
});

it('updates employee from DTO', function (): void {
    $created = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-006', fullName: 'Original', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    $updated = $this->service->update($created->id, new UpdateEmployeeDTO(
        employeeCode: null, fullName: 'Updated Name', branchId: null,
        employmentStatus: null, hireDate: null, resignationDate: null,
        position: 'Manager', gender: null, religion: null, maritalStatus: null,
        nationalityId: null, phone: null, email: null, address: null,
        districtId: null, villageId: null,
    ), $this->orgId);

    expect($updated->full_name)->toBe('Updated Name');
    expect($updated->position)->toBe('Manager');
    expect($updated->employee_code)->toBe('EMP-006');
});

it('throws BusinessException on update with duplicate employee_code', function (): void {
    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-007', fullName: 'First', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));
    $second = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-008', fullName: 'Second', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    expect(fn () => $this->service->update($second->id, new UpdateEmployeeDTO(
        employeeCode: 'EMP-007', fullName: null, branchId: null,
        employmentStatus: null, hireDate: null, resignationDate: null,
        position: null, gender: null, religion: null, maritalStatus: null,
        nationalityId: null, phone: null, email: null, address: null,
        districtId: null, villageId: null,
    ), $this->orgId))->toThrow(BusinessException::class, 'Employee code already taken.');
});

it('soft-deletes employee', function (): void {
    $created = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-009', fullName: 'Deletable', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();

    $deleted = Employee::withTrashed()->find($created->id);
    expect($deleted->deleted_at)->not->toBeNull();
    expect(Employee::find($created->id))->toBeNull();
});

it('toggleActive flips is_active', function (): void {
    $created = $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-012', fullName: 'Toggle', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));

    expect($created->is_active)->toBeTrue();

    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->is_active)->toBeFalse();

    $toggledAgain = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggledAgain->is_active)->toBeTrue();
});

it('EmployeeService implements EmployeeServiceInterface', function (): void {
    expect($this->service)->toBeInstanceOf(EmployeeServiceInterface::class);
});

it('create wraps in DB transaction', function (): void {
    DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

    $this->service->create(new CreateEmployeeDTO(
        employeeCode: 'EMP-013', fullName: 'Tx', organizationId: $this->orgId,
        branchId: null, employmentStatus: 'active', hireDate: '2026-01-01',
        resignationDate: null, position: null, gender: null, religion: null,
        maritalStatus: null, nationalityId: null, phone: null, email: null,
        address: null, districtId: null, villageId: null,
    ));
});