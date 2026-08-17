<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Billing\DTO\CreateBillingDTO;
use App\Domains\Billing\DTO\UpdateBillingDTO;
use App\Domains\Billing\Interfaces\BillingServiceInterface;
use App\Domains\Billing\Models\Billing;
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
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-BILL-01', 'company_name' => 'Billing Test Org',
        'email' => 'bill@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-BILL-01',
        'full_name' => 'Billing Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(BillingServiceInterface::class);
});

it('creates invoice from DTO and returns Billing model', function (): void {
    $dto = new CreateBillingDTO(
        totalAmount: '500000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
        paidAmount: null,
        dueDate: null,
        items: null,
        notes: null,
    );

    $billing = $this->service->create($dto);

    expect($billing)->toBeInstanceOf(Billing::class);
    expect($billing->total_amount)->toBe('500000.00');
    expect($billing->organization_id)->toBe($this->orgId);
    expect($billing->status)->toBe('draft');
    expect($billing->invoice_number)->toStartWith('INV-');
});

it('throws BusinessException when status transition is invalid (paid → sent)', function (): void {
    $created = $this->service->create(new CreateBillingDTO(
        totalAmount: '100000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    $this->service->update($created->id, new UpdateBillingDTO(
        status: 'sent',
    ), $this->orgId);

    $this->service->update($created->id, new UpdateBillingDTO(
        paidAmount: '100000',
        totalAmount: '100000',
    ), $this->orgId);

    expect(fn () => $this->service->update($created->id, new UpdateBillingDTO(
        status: 'sent',
    ), $this->orgId))->toThrow(BusinessException::class);
});

it('finds invoice by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateBillingDTO(
        totalAmount: '750000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->total_amount)->toBe('750000.00');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException when id belongs to different organization', function (): void {
    $created = $this->service->create(new CreateBillingDTO(
        totalAmount: '200000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates invoices scoped to organization', function (): void {
    $this->service->create(new CreateBillingDTO(
        totalAmount: '100000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));
    $this->service->create(new CreateBillingDTO(
        totalAmount: '200000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates invoice from DTO', function (): void {
    $created = $this->service->create(new CreateBillingDTO(
        totalAmount: '300000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    $updated = $this->service->update($created->id, new UpdateBillingDTO(
        totalAmount: '500000',
        notes: 'Updated notes',
    ), $this->orgId);

    expect($updated->total_amount)->toBe('500000.00');
    expect($updated->notes)->toBe('Updated notes');
});

it('soft-deletes invoice', function (): void {
    $created = $this->service->create(new CreateBillingDTO(
        totalAmount: '400000',
        organizationId: $this->orgId,
        patientId: $this->patientId,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(Billing::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});