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
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-CRM-01', 'company_name' => 'CRM Test Org',
        'email' => 'crm@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-CRM-01',
        'branch_name' => 'CRM Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test CRM', 'email' => 'test-crm@test.com', 'username' => 'testcrm',
        'employee_code' => 'EMP-CRM-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-CRM-01',
        'full_name' => 'CRM Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates CRM contact and returns 201', function (): void {
        $response = $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'inquiry',
            'patient_id'   => $this->patientId,
            'channel'      => 'phone',
            'subject'      => 'Test inquiry',
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.contact_type', 'inquiry');
        $response->assertJsonPath('data.status', 'new');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/crm-contacts', [])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'complaint',
        ])->assertStatus(201);

        $this->putJson('/api/v1/crm-contacts/' . $c->json('data.id'), ['status' => 'closed'])->assertStatus(200);

        $this->putJson('/api/v1/crm-contacts/' . $c->json('data.id'), ['status' => 'in_progress'])
            ->assertStatus(422);
    });

    it('lists CRM contacts', function (): void {
        $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'inquiry',
        ])->assertStatus(201);
        $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'complaint',
        ])->assertStatus(201);
        $this->getJson('/api/v1/crm-contacts')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows CRM contact by id', function (): void {
        $c = $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'inquiry', 'subject' => 'Show me',
        ])->assertStatus(201);
        $this->getJson('/api/v1/crm-contacts/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.contact_type', 'inquiry');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/crm-contacts/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates CRM contact', function (): void {
        $c = $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'inquiry',
        ])->assertStatus(201);
        $this->putJson('/api/v1/crm-contacts/' . $c->json('data.id'), [
            'status'  => 'in_progress',
            'subject' => 'Updated subject',
            'message' => 'Updated message',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.subject', 'Updated subject');
    });

    it('soft-deletes CRM contact', function (): void {
        $c = $this->postJson('/api/v1/crm-contacts', [
            'contact_type' => 'inquiry',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/crm-contacts/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });
});

it('requires authentication', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();
    $this->postJson('/api/v1/crm-contacts', [
        'contact_type' => 'inquiry',
    ])->assertStatus(401);
});