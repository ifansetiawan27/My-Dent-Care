<?php

declare(strict_types=1);

use App\Domains\AI\Enums\AIStatus;
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
        'id' => $this->orgId, 'company_code' => 'ORG-AI-01', 'company_name' => 'AI Test Org',
        'email' => 'ai@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('branches')->insert([
        'id' => $this->branchId, 'organization_id' => $this->orgId, 'branch_code' => 'BRC-AI-01',
        'branch_name' => 'AI Test Branch', 'branch_type' => 'clinic', 'phone' => '081234567891',
        'address' => 'Jl. Test', 'city' => 'Jakarta', 'province' => 'DKI Jakarta',
        'postal_code' => '12345', 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('users')->insert([
        'id' => $this->userId, 'organization_id' => $this->orgId, 'branch_id' => $this->branchId,
        'name' => 'AI Tester', 'email' => 'ai-tester@test.com', 'username' => 'aitester',
        'employee_code' => 'EMP-AI-01', 'password' => 'hashed', 'phone' => '081234567892',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->actingAs(\App\Domains\User\Models\User::find($this->userId));
});

it('creates AI query and returns 201', function (): void {
    $response = $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'What are the symptoms of periodontitis?',
        'model'      => 'gpt-4',
    ]);
    $response->assertStatus(201);
    $response->assertJsonPath('status', AIStatus::Pending->value);
    $response->assertJsonPath('query_type', 'diagnosis_suggestion');
});

it('validates required fields', function (): void {
    $this->postJson('/api/v1/ai-queries', [])->assertStatus(422);
});

it('validates query_type max length', function (): void {
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => Str::random(51),
        'prompt'     => 'Test',
    ])->assertStatus(422);
});

it('lists AI queries', function (): void {
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'Test prompt 1',
    ])->assertStatus(201);
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'treatment_recommendation',
        'prompt'     => 'Test prompt 2',
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/ai-queries');
    $response->assertStatus(200);
    $data = $response->json('data');
    expect(count($data))->toBe(2);
});

it('filters AI queries by query_type', function (): void {
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'Test prompt 1',
    ])->assertStatus(201);
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'treatment_recommendation',
        'prompt'     => 'Test prompt 2',
    ])->assertStatus(201);

    $response = $this->getJson('/api/v1/ai-queries?query_type=diagnosis_suggestion');
    $response->assertStatus(200);
    $data = $response->json('data');
    expect(count($data))->toBe(1);
    expect($data[0]['query_type'])->toBe('diagnosis_suggestion');
});

it('shows AI query by id', function (): void {
    $c = $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'report_summary',
        'prompt'     => 'Summarize monthly visits',
    ])->assertStatus(201);

    $this->getJson('/api/v1/ai-queries/' . $c->json('id'))
        ->assertStatus(200);
});

it('returns 404 for nonexistent', function (): void {
    $this->getJson('/api/v1/ai-queries/' . (string) Str::orderedUuid())->assertStatus(404);
});

it('retries failed query', function (): void {
    $c = $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'Test prompt',
    ])->assertStatus(201);

    $id = $c->json('id');
    DB::table('ai_queries')->where('id', $id)->update([
        'status'        => AIStatus::Failed->value,
        'error_message' => 'Model timeout',
    ]);

    $this->postJson('/api/v1/ai-queries/' . $id . '/retry')
        ->assertStatus(200)
        ->assertJsonPath('status', AIStatus::Pending->value)
        ->assertJsonPath('error_message', null);
});

it('rejects retry on non-failed query', function (): void {
    $c = $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'Test prompt',
    ])->assertStatus(201);

    $this->postJson('/api/v1/ai-queries/' . $c->json('id') . '/retry')
        ->assertStatus(422);
});

it('cancels pending query', function (): void {
    $c = $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'diagnosis_suggestion',
        'prompt'     => 'Test prompt',
    ])->assertStatus(201);

    $this->postJson('/api/v1/ai-queries/' . $c->json('id') . '/cancel')
        ->assertStatus(200)
        ->assertJsonPath('status', AIStatus::Failed->value)
        ->assertJsonPath('error_message', 'Cancelled by user');
});

it('returns 401 when unauthenticated', function (): void {
    $this->app['auth']->guard('sanctum')->forgetUser();

    $this->getJson('/api/v1/ai-queries')->assertStatus(401);
    $this->postJson('/api/v1/ai-queries', [
        'query_type' => 'test',
        'prompt'     => 'Test',
    ])->assertStatus(401);
    $this->getJson('/api/v1/ai-queries/' . (string) Str::orderedUuid())->assertStatus(401);
    $this->postJson('/api/v1/ai-queries/' . (string) Str::orderedUuid() . '/retry')->assertStatus(401);
    $this->postJson('/api/v1/ai-queries/' . (string) Str::orderedUuid() . '/cancel')->assertStatus(401);
});