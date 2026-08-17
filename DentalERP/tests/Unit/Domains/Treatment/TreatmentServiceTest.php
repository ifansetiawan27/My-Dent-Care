<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Treatment\DTO\CreateTreatmentDTO;
use App\Domains\Treatment\DTO\UpdateTreatmentDTO;
use App\Domains\Treatment\Interfaces\TreatmentServiceInterface;
use App\Domains\Treatment\Models\Treatment;
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
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-TRT-01', 'company_name' => 'Treatment Test Org',
        'email' => 'trt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-TRT-01',
        'branch_name' => 'Treatment Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-TRT-01',
        'full_name' => 'Treatment Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(TreatmentServiceInterface::class);
});

it('creates treatment from DTO and returns Treatment model', function (): void {
    $dto = new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Cleaning',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    );

    $treatment = $this->service->create($dto);

    expect($treatment)->toBeInstanceOf(Treatment::class);
    expect($treatment->treatment_type)->toBe('Cleaning');
    expect($treatment->patient_id)->toBe($this->patientId);
    expect($treatment->organization_id)->toBe($this->orgId);
    expect($treatment->status)->toBe('planned');
});

it('throws BusinessException when status transition is invalid (completed → in_progress)', function (): void {
    $created = $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Filling',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    $this->service->update($created->id, new UpdateTreatmentDTO(
        status: 'in_progress',
    ), $this->orgId);

    $this->service->update($created->id, new UpdateTreatmentDTO(
        status: 'completed',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateTreatmentDTO(
        status: 'in_progress',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds treatment by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Extraction',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->treatment_type)->toBe('Extraction');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Whitening',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates treatments scoped to organization', function (): void {
    $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Checkup',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));
    $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'X-Ray',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates treatment from DTO', function (): void {
    $created = $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Original',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    $updated = $this->service->update($created->id, new UpdateTreatmentDTO(
        treatmentType: 'Updated',
        cost: '500000',
        description: 'Updated description',
    ), $this->orgId);

    expect($updated->treatment_type)->toBe('Updated');
    expect($updated->cost)->toBe('500000.00');
    expect($updated->description)->toBe('Updated description');
});

it('soft-deletes treatment', function (): void {
    $created = $this->service->create(new CreateTreatmentDTO(
        patientId: $this->patientId,
        treatmentType: 'Deletable',
        organizationId: $this->orgId,
        doctorId: null,
        appointmentId: null,
        cost: null,
        description: null,
        procedureData: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Treatment::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});