<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Appointment\DTO\CreateAppointmentDTO;
use App\Domains\Appointment\DTO\UpdateAppointmentDTO;
use App\Domains\Appointment\Interfaces\AppointmentServiceInterface;
use App\Domains\Appointment\Models\Appointment;
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
        'id' => $this->orgId, 'company_code' => 'ORG-APT-01', 'company_name' => 'Appointment Test Org',
        'email' => 'apt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-APT-01',
        'branch_name' => 'Appointment Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(AppointmentServiceInterface::class);
});

it('creates appointment from DTO', function (): void {
    $dto = new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: $this->branchId, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: now()->addDay()->addHour()->toDateTimeString(), status: 'scheduled',
        type: 'checkup', notes: null,
    );

    $appointment = $this->service->create($dto);
    expect($appointment)->toBeInstanceOf(Appointment::class);
    expect($appointment->status)->toBe('scheduled');
    expect($appointment->type)->toBe('checkup');
});

it('handles duplicate appointment creation', function (): void {
    $dto = new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: $this->branchId, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    );

    $first = $this->service->create($dto);
    $second = $this->service->create($dto);
    expect($first->id)->not->toBe($second->id);
});

it('finds appointment by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: $this->branchId, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    ));
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $created = $this->service->create(new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: null, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    ));
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('updates appointment from DTO', function (): void {
    $created = $this->service->create(new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: null, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    ));
    $updated = $this->service->update($created->id, new UpdateAppointmentDTO(
        status: 'confirmed', notes: 'Updated notes',
    ), $this->orgId);
    expect($updated->status)->toBe('confirmed');
    expect($updated->notes)->toBe('Updated notes');
});

it('soft-deletes appointment', function (): void {
    $created = $this->service->create(new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: null, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    ));
    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();
    expect(Appointment::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips status', function (): void {
    $created = $this->service->create(new CreateAppointmentDTO(
        organizationId: $this->orgId, branchId: null, patientId: null,
        doctorId: null, scheduledAt: now()->addDay()->toDateTimeString(),
        endAt: null, status: 'scheduled', type: null, notes: null,
    ));
    expect($created->status)->toBe('scheduled');
    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->status)->toBe('cancelled');
});