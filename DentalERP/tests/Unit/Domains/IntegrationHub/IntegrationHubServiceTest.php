<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\IntegrationHub\DTO\CreateIntegrationDTO;
use App\Domains\IntegrationHub\DTO\UpdateIntegrationDTO;
use App\Domains\IntegrationHub\Interfaces\IntegrationHubServiceInterface;
use App\Domains\IntegrationHub\Models\IntegrationHub;
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
        'id'           => $this->orgId,
        'company_code' => 'ORG-INT-01',
        'company_name' => 'Integration Test Org',
        'email'        => 'int@test.com',
        'phone'        => '081234567890',
        'address'      => 'Jl. Test',
        'city'         => 'Jakarta',
        'province'     => 'DKI Jakarta',
        'postal_code'  => '12345',
        'created_at'   => $now,
        'updated_at'   => $now,
    ]);

    $this->service = app(IntegrationHubServiceInterface::class);
});

it('creates integration config from DTO', function (): void {
    $dto = new CreateIntegrationDTO(
        provider:       'satusehat',
        name:           'SATUSEHAT Bridge',
        organizationId: $this->orgId,
        config:         ['api_url' => 'https://api.satusehat.kemkes.go.id'],
        credentials:    ['client_id' => 'test-client', 'client_secret' => 'test-secret'],
    );

    $integration = $this->service->create($dto);

    expect($integration)->toBeInstanceOf(IntegrationHub::class);
    expect($integration->provider)->toBe('satusehat');
    expect($integration->name)->toBe('SATUSEHAT Bridge');
    expect($integration->organization_id)->toBe($this->orgId);
    expect($integration->is_active)->toBeFalse();
    expect($integration->config)->toBe(['api_url' => 'https://api.satusehat.kemkes.go.id']);
});

it('throws BusinessException for duplicate provider per organization', function (): void {
    $this->service->create(new CreateIntegrationDTO(
        provider:       'bpjs',
        name:           'BPJS First',
        organizationId: $this->orgId,
    ));

    expect(fn () => $this->service->create(new CreateIntegrationDTO(
        provider:       'bpjs',
        name:           'BPJS Second',
        organizationId: $this->orgId,
    )))->toThrow(BusinessException::class, 'Provider already exists for this organization.');
});

it('finds by id scoped to organization', function (): void {
    $created = $this->service->create(new CreateIntegrationDTO(
        provider:       'payment-gateway',
        name:           'Payment Gateway',
        organizationId: $this->orgId,
    ));

    $found = $this->service->findById($created->id, $this->orgId);

    expect($found->id)->toBe($created->id);
    expect($found->provider)->toBe('payment-gateway');
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for cross-organization access', function (): void {
    $created = $this->service->create(new CreateIntegrationDTO(
        provider:       'sms-gateway',
        name:           'SMS Gateway',
        organizationId: $this->orgId,
    ));

    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('paginates integrations scoped to organization', function (): void {
    $this->service->create(new CreateIntegrationDTO(
        provider:       'satusehat',
        name:           'SATUSEHAT',
        organizationId: $this->orgId,
    ));
    $this->service->create(new CreateIntegrationDTO(
        provider:       'bpjs',
        name:           'BPJS',
        organizationId: $this->orgId,
    ));

    $result = $this->service->paginate(['organization_id' => $this->orgId]);

    expect($result->total())->toBe(2);
});

it('updates integration config from DTO', function (): void {
    $created = $this->service->create(new CreateIntegrationDTO(
        provider:       'original',
        name:           'Original Name',
        organizationId: $this->orgId,
    ));

    $updated = $this->service->update($created->id, new UpdateIntegrationDTO(
        name:   'Updated Name',
        config: ['new_key' => 'new_value'],
    ), $this->orgId);

    expect($updated->name)->toBe('Updated Name');
    expect($updated->config)->toBe(['new_key' => 'new_value']);
});

it('soft-deletes integration config', function (): void {
    $created = $this->service->create(new CreateIntegrationDTO(
        provider:       'deletable',
        name:           'Delete Me',
        organizationId: $this->orgId,
    ));

    $result = $this->service->delete($created->id, $this->orgId);

    expect($result)->toBeTrue();
    expect(IntegrationHub::withTrashed()->find($created->id)->deleted_at)->not->toBeNull();
});