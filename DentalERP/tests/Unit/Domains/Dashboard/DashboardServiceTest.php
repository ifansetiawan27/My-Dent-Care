<?php

declare(strict_types=1);

use App\Core\Exceptions\NotFoundException;
use App\Domains\Dashboard\DTO\CreateDashboardDTO;
use App\Domains\Dashboard\DTO\UpdateDashboardDTO;
use App\Domains\Dashboard\Interfaces\DashboardServiceInterface;
use App\Domains\Dashboard\Models\Dashboard;
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
    $this->userId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-DASH-01', 'company_name' => 'Dashboard Test Org',
        'email' => 'dash@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-DASH-01',
        'branch_name' => 'Dashboard Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Dashboard User', 'email' => 'dashuser@test.com', 'username' => 'dashuser',
        'employee_code' => 'EMP-DASH-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(DashboardServiceInterface::class);
});

it('creates dashboard from DTO and returns Dashboard model', function (): void {
    $dto = new CreateDashboardDTO(
        name: 'My Dashboard',
        organizationId: $this->orgId,
        userId: $this->userId,
        config: null,
        widgets: null,
        isDefault: null,
    );

    $dashboard = $this->service->create($dto);

    expect($dashboard)->toBeInstanceOf(Dashboard::class);
    expect($dashboard->name)->toBe('My Dashboard');
    expect($dashboard->organization_id)->toBe($this->orgId);
    expect($dashboard->user_id)->toBe($this->userId);
    expect($dashboard->is_default)->toBeFalse();
});

it('finds dashboard by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateDashboardDTO(
        name: 'Dashboard 1', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->name)->toBe('Dashboard 1');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateDashboardDTO(
        name: 'Dashboard 2', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates dashboards scoped to organization', function (): void {
    $this->service->create(new CreateDashboardDTO(
        name: 'Dashboard A', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));
    $this->service->create(new CreateDashboardDTO(
        name: 'Dashboard B', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates dashboard from DTO', function (): void {
    $created = $this->service->create(new CreateDashboardDTO(
        name: 'Original', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));

    $updated = $this->service->update($created->id, new UpdateDashboardDTO(
        name: 'Updated Dashboard',
        config: ['theme' => 'dark'],
        widgets: [['type' => 'chart', 'id' => 'widget-1']],
        isDefault: true,
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Dashboard');
    expect($updated->config)->toBe(['theme' => 'dark']);
    expect($updated->widgets)->toEqual([['type' => 'chart', 'id' => 'widget-1']]);
    expect($updated->is_default)->toBeTrue();
});

it('soft-deletes dashboard', function (): void {
    $created = $this->service->create(new CreateDashboardDTO(
        name: 'Deletable', organizationId: $this->orgId,
        userId: null, config: null, widgets: null, isDefault: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Dashboard::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});