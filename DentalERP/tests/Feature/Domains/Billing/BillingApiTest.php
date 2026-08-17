<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->orgId     = (string) Str::orderedUuid();
    $this->branchId  = (string) Str::orderedUuid();
    $this->userId    = (string) Str::orderedUuid();
    $this->patientId = (string) Str::orderedUuid();
    $now = now();

    DB::table('organizations')->insert([
        'id' => $this->orgId, 'company_code' => 'ORG-BILL-01', 'company_name' => 'Billing Test Org',
        'email' => 'bill@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-BILL-01',
        'branch_name' => 'Billing Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'Test Billing', 'email' => 'test-bill@test.com', 'username' => 'testbill',
        'employee_code' => 'EMP-BILL-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('patients')->insert([
        'id' => $this->patientId, 'organization_id' => $this->orgId, 'patient_code' => 'PAT-BILL-01',
        'full_name' => 'Billing Patient', 'created_at' => $now, 'updated_at' => $now,
    ]);
});

describe('authenticated', function () {
    beforeEach(function (): void {
        $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
    });

    it('creates invoice and returns 201', function (): void {
        $response = $this->postJson('/api/v1/invoices', [
            'patient_id'   => $this->patientId,
            'total_amount' => '500000',
            'due_date'     => '2026-12-31',
        ]);
        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'draft');
        $response->assertJsonPath('data.total_amount', '500000.00');
    });

    it('validates required fields', function (): void {
        $this->postJson('/api/v1/invoices', [])->assertStatus(422);
    });

    it('rejects invalid status transition', function (): void {
        $c = $this->postJson('/api/v1/invoices', [
            'patient_id'   => $this->patientId,
            'total_amount' => '100000',
        ])->assertStatus(201);

        $this->putJson('/api/v1/invoices/' . $c->json('data.id'), ['status' => 'paid'])
            ->assertStatus(422);
    });

    it('lists invoices', function (): void {
        $this->postJson('/api/v1/invoices', [
            'total_amount' => '100000',
        ])->assertStatus(201);
        $this->postJson('/api/v1/invoices', [
            'total_amount' => '200000',
        ])->assertStatus(201);
        $this->getJson('/api/v1/invoices')->assertStatus(200)->assertJsonCount(2, 'data');
    });

    it('shows invoice by id', function (): void {
        $c = $this->postJson('/api/v1/invoices', [
            'patient_id'   => $this->patientId,
            'total_amount' => '300000',
        ])->assertStatus(201);
        $this->getJson('/api/v1/invoices/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('data.total_amount', '300000.00');
    });

    it('returns 404 for nonexistent', function (): void {
        $this->getJson('/api/v1/invoices/' . (string) Str::orderedUuid())->assertStatus(404);
    });

    it('updates invoice', function (): void {
        $c = $this->postJson('/api/v1/invoices', [
            'total_amount' => '400000',
        ])->assertStatus(201);
        $this->putJson('/api/v1/invoices/' . $c->json('data.id'), [
            'total_amount' => '600000',
            'notes'        => 'Updated',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.total_amount', '600000.00')
            ->assertJsonPath('data.notes', 'Updated');
    });

    it('soft-deletes invoice', function (): void {
        $c = $this->postJson('/api/v1/invoices', [
            'total_amount' => '500000',
        ])->assertStatus(201);
        $this->deleteJson('/api/v1/invoices/' . $c->json('data.id'))
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    });

    it('transitions status from draft to sent', function (): void {
        $c = $this->postJson('/api/v1/invoices', [
            'total_amount' => '700000',
        ])->assertStatus(201);
        $this->putJson('/api/v1/invoices/' . $c->json('data.id'), ['status' => 'sent'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'sent');
    });
});

it('requires authentication', function (): void {
    $this->postJson('/api/v1/invoices', [
        'patient_id'   => $this->patientId,
        'total_amount' => '100000',
    ])->assertStatus(401);
});