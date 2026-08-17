<?php

declare(strict_types=1);

use App\Core\Exceptions\NotFoundException;
use App\Domains\Reporting\DTO\CreateReportingDTO;
use App\Domains\Reporting\DTO\UpdateReportingDTO;
use App\Domains\Reporting\Interfaces\ReportingServiceInterface;
use App\Domains\Reporting\Models\Reporting;
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
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-RPT-01', 'company_name' => 'Reporting Test Org',
        'email' => 'rpt@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(ReportingServiceInterface::class);
});

it('creates report from DTO and returns Reporting model', function (): void {
    $dto = new CreateReportingDTO(
        reportType: 'financial',
        name: 'Monthly Revenue',
        reportDate: '2026-08-17',
        organizationId: $this->orgId,
        parameters: null,
        data: null,
    );

    $report = $this->service->create($dto);

    expect($report)->toBeInstanceOf(Reporting::class);
    expect($report->report_type)->toBe('financial');
    expect($report->name)->toBe('Monthly Revenue');
    expect($report->organization_id)->toBe($this->orgId);
    expect($report->status)->toBe('generated');
});

it('finds report by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateReportingDTO(
        reportType: 'financial', name: 'Report 1', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->name)->toBe('Report 1');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateReportingDTO(
        reportType: 'financial', name: 'Report 2', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates reports scoped to organization', function (): void {
    $this->service->create(new CreateReportingDTO(
        reportType: 'financial', name: 'Report A', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));
    $this->service->create(new CreateReportingDTO(
        reportType: 'inventory', name: 'Report B', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates report from DTO', function (): void {
    $created = $this->service->create(new CreateReportingDTO(
        reportType: 'financial', name: 'Original', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));

    $updated = $this->service->update($created->id, new UpdateReportingDTO(
        name: 'Updated Report',
        status: 'archived',
        data: ['revenue' => 5000000],
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Report');
    expect($updated->status)->toBe('archived');
    expect($updated->data)->toBe(['revenue' => 5000000]);
});

it('soft-deletes report', function (): void {
    $created = $this->service->create(new CreateReportingDTO(
        reportType: 'financial', name: 'Deletable', reportDate: '2026-08-17',
        organizationId: $this->orgId, parameters: null, data: null,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Reporting::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});