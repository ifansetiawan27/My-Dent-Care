<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->orgId    = (string) Str::orderedUuid();
    $this->branchId = (string) Str::orderedUuid();
    $this->userId   = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-INT-API', 'company_name' => 'Int API Test Org',
        'email' => 'int-api@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-INT-01',
        'branch_name' => 'Int API Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Int API', 'email' => 'test-int-api@test.com', 'username' => 'testintapi',
        'employee_code' => 'EMP-INT-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates integration config and returns 201', function (): void {
        $response = $this->postJson('/api/v1/integration-configs', [
            'provider' => 'satusehat',
            'name'     => 'SATUSEHAT Bridge',
            'config'   => ['api_url' => 'https://api.satusehat.kemkes.go.id'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.provider', 'satusehat');
        $response->assertJsonPath('data.name', 'SATUSEHAT Bridge');
        $response->assertJsonPath('data.is_active', false);
    });

    it('does not expose credentials in create response', function (): void {
        $response = $this->postJson('/api/v1/integration-configs', [
            'provider'    => 'bpjs',
            'name'        => 'BPJS API',
            'credentials' => ['client_id' => 'secret-id', 'client_secret' => 'secret-key'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonMissing(['data' => ['credentials']]);
        $response->assertJsonMissingPath('data.credentials');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/integration-configs', [])->assertStatus(422);
    });

    it('rejects duplicate provider per organization', function (): void {
        $this->postJson('/api/v1/integration-configs', [
            'provider' => 'unique-provider',
            'name'     => 'First',
        ])->assertStatus(201);

        $this->postJson('/api/v1/integration-configs', [
            'provider' => 'unique-provider',
            'name'     => 'Second',
        ])->assertStatus(422);
    });

    it('lists integration configs', function (): void {
        $this->postJson('/api/v1/integration-configs', [
            'provider' => 'provider-one', 'name' => 'Integration One',
        ])->assertStatus(201);
        $this->postJson('/api/v1/integration-configs', [
            'provider' => 'provider-two', 'name' => 'Integration Two',
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/integration-configs');
        $response->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('does not expose credentials in list response', function (): void {
        $this->postJson('/api/v1/integration-configs', [
            'provider'    => 'provider-a',
            'name'        => 'Test A',
            'credentials' => ['api_key' => 'secret-123'],
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/integration-configs');
        $response->assertStatus(200);
        $response->assertJsonMissing(['data' => [['credentials']]]);

        $items = $response->json('data');
        foreach ($items as $item) {
            expect($item)->not->toHaveKey('credentials');
        }
    });

    it('shows integration config by id', function (): void {
        $c = $this->postJson('/api/v1/integration-configs', [
            'provider' => 'show-me', 'name' => 'Show Me',
        ])->assertStatus(201);

        $this->getJson('/api/v1/integration-configs/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.provider', 'show-me');
    });

    it('does not expose credentials in show response', function (): void {
        $c = $this->postJson('/api/v1/integration-configs', [
            'provider'    => 'secret-provider',
            'name'        => 'Secret Config',
            'credentials' => ['token' => 'super-secret-token'],
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/integration-configs/' . $c->json('data.id'));
        $response->assertStatus(200);
        $response->assertJsonMissing(['data' => ['credentials']]);
        $response->assertJsonMissingPath('data.credentials');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/integration-configs/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates integration config', function (): void {
        $c = $this->postJson('/api/v1/integration-configs', [
            'provider' => 'update-me', 'name' => 'Before Update',
        ])->assertStatus(201);

        $this->putJson('/api/v1/integration-configs/' . $c->json('data.id'), [
            'name'     => 'After Update',
            'is_active' => true,
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'After Update')
            ->assertJsonPath('data.is_active', true);
    });

    it('toggles active status', function (): void {
        $c = $this->postJson('/api/v1/integration-configs', [
            'provider' => 'toggle-me', 'name' => 'Toggle Me',
        ])->assertStatus(201);

        expect($c->json('data.is_active'))->toBeFalse();

        $this->postJson('/api/v1/integration-configs/' . $c->json('data.id') . '/toggle-active')
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', true);

        $this->postJson('/api/v1/integration-configs/' . $c->json('data.id') . '/toggle-active')
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', false);
    });

    it('soft-deletes integration config', function (): void {
        $c = $this->postJson('/api/v1/integration-configs', [
            'provider' => 'delete-me', 'name' => 'Delete Me',
        ])->assertStatus(201);

        $this->deleteJson('/api/v1/integration-configs/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/integration-configs', [
        'provider' => 'no-auth',
        'name'     => 'No Auth',
    ])->assertStatus(401);
});