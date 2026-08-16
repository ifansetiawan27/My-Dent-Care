<?php

declare(strict_types=1);

use App\Core\Exceptions\NotFoundException;
use App\Domains\Odontogram\DTO\CreateOdontogramDTO;
use App\Domains\Odontogram\DTO\UpdateOdontogramDTO;
use App\Domains\Odontogram\Interfaces\OdontogramServiceInterface;
use App\Domains\Odontogram\Models\Odontogram;
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
        'id' => $this->orgId, 'company_code' => 'ORG-ODO-01', 'company_name' => 'Odontogram Test Org',
        'email' => 'odo@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-ODO-01',
        'branch_name' => 'Odontogram Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-ODO-01',
        'full_name' => 'Odontogram Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(OdontogramServiceInterface::class);
});

it('creates odontogram from DTO', function (): void {
    $dto = new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '11',
        toothType: 'permanent', surface: 'occlusal', condition: 'healthy',
        notes: null, findings: null,
    );

    $odontogram = $this->service->create($dto);
    expect($odontogram)->toBeInstanceOf(Odontogram::class);
    expect($odontogram->tooth_number)->toBe('11');
    expect($odontogram->condition)->toBe('healthy');
});

it('handles duplicate odontogram creation', function (): void {
    $dto = new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '12',
        toothType: null, surface: null, condition: null, notes: null, findings: null,
    );

    $first = $this->service->create($dto);
    $second = $this->service->create($dto);
    expect($first->id)->not->toBe($second->id);
});

it('finds odontogram by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '13',
        toothType: null, surface: null, condition: null, notes: null, findings: null,
    ));
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $created = $this->service->create(new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '14',
        toothType: null, surface: null, condition: null, notes: null, findings: null,
    ));
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('updates odontogram from DTO', function (): void {
    $created = $this->service->create(new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '15',
        toothType: null, surface: null, condition: null, notes: null, findings: null,
    ));
    $updated = $this->service->update($created->id, new UpdateOdontogramDTO(
        condition: 'caries', notes: 'Needs filling',
    ), $this->orgId);
    expect($updated->condition)->toBe('caries');
    expect($updated->notes)->toBe('Needs filling');
});

it('soft-deletes odontogram', function (): void {
    $created = $this->service->create(new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '16',
        toothType: null, surface: null, condition: null, notes: null, findings: null,
    ));
    $result = $this->service->delete($created->id, $this->orgId);
    expect($result)->toBeTrue();
    expect(Odontogram::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});

it('toggleActive flips condition', function (): void {
    $created = $this->service->create(new CreateOdontogramDTO(
        organizationId: $this->orgId, patientId: $this->patientId, toothNumber: '17',
        toothType: null, surface: null, condition: 'healthy', notes: null, findings: null,
    ));
    expect($created->condition)->toBe('healthy');
    $toggled = $this->service->toggleActive($created->id, $this->orgId);
    expect($toggled->condition)->toBe('caries');
});