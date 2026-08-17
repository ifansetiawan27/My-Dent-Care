<?php

declare(strict_types=1);

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\AI\DTO\CreateAIDTO;
use App\Domains\AI\Enums\AIStatus;
use App\Domains\AI\Interfaces\AIServiceInterface;
use App\Domains\AI\Models\AI;
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
        'id' => $this->orgId, 'company_code' => 'ORG-AI-01', 'company_name' => 'AI Test Org',
        'email' => 'ai@test.com', 'phone' => '081234567890', 'address' => 'Jl. Test',
        'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12345',
        'created_at' => $now, 'updated_at' => $now,
    ]);

    $this->service = app(AIServiceInterface::class);
});

it('creates AI query from DTO', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'diagnosis_suggestion',
        prompt: 'What are the symptoms of periodontitis?',
        model: 'gpt-4',
    );

    $ai = $this->service->create($dto);
    expect($ai)->toBeInstanceOf(AI::class);
    expect($ai->status)->toBe(AIStatus::Pending->value);
    expect($ai->query_type)->toBe('diagnosis_suggestion');
    expect($ai->prompt)->toBe('What are the symptoms of periodontitis?');
    expect($ai->model)->toBe('gpt-4');
});

it('creates AI query with default status pending', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'report_summary',
        prompt: 'Summarize patient visits this month',
        model: null,
    );

    $ai = $this->service->create($dto);
    expect($ai->status)->toBe(AIStatus::Pending->value);
    expect($ai->tokens_used)->toBeNull();
    expect($ai->error_message)->toBeNull();
});

it('finds AI query by id scoped to organization', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'treatment_recommendation',
        prompt: 'Suggest treatment for cavity',
        model: null,
    );

    $created = $this->service->create($dto);
    $found = $this->service->findById($created->id, $this->orgId);
    expect($found->id)->toBe($created->id);
});

it('throws NotFoundException for nonexistent id', function (): void {
    expect(fn () => $this->service->findById((string) Str::orderedUuid(), $this->orgId))
        ->toThrow(NotFoundException::class);
});

it('throws NotFoundException for different organization', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'patient_insight',
        prompt: 'Analyze patient history',
        model: null,
    );

    $created = $this->service->create($dto);
    expect(fn () => $this->service->findById($created->id, (string) Str::orderedUuid()))
        ->toThrow(NotFoundException::class);
});

it('retries failed query', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'diagnosis_suggestion',
        prompt: 'Test prompt',
        model: null,
    );

    $created = $this->service->create($dto);

    DB::table('ai_queries')->where('id', $created->id)->update([
        'status'        => AIStatus::Failed->value,
        'error_message' => 'Model timeout',
    ]);

    $retried = $this->service->retry($created->id, $this->orgId);
    expect($retried->status)->toBe(AIStatus::Pending->value);
    expect($retried->error_message)->toBeNull();
});

it('rejects retry on non-failed query', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'diagnosis_suggestion',
        prompt: 'Test prompt',
        model: null,
    );

    $created = $this->service->create($dto);
    expect($created->status)->toBe(AIStatus::Pending->value);

    expect(fn () => $this->service->retry($created->id, $this->orgId))
        ->toThrow(BusinessException::class, 'Only failed queries can be retried.');
});

it('cancels pending query', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'diagnosis_suggestion',
        prompt: 'Test prompt',
        model: null,
    );

    $created = $this->service->create($dto);
    $cancelled = $this->service->cancel($created->id, $this->orgId);
    expect($cancelled->status)->toBe(AIStatus::Failed->value);
    expect($cancelled->error_message)->toBe('Cancelled by user');
});

it('rejects cancel on completed query', function (): void {
    $dto = new CreateAIDTO(
        organizationId: $this->orgId,
        userId: null,
        queryType: 'diagnosis_suggestion',
        prompt: 'Test prompt',
        model: null,
    );

    $created = $this->service->create($dto);

    DB::table('ai_queries')->where('id', $created->id)->update([
        'status'      => AIStatus::Completed->value,
        'response'    => 'Test response',
        'tokens_used' => 100,
    ]);

    expect(fn () => $this->service->cancel($created->id, $this->orgId))
        ->toThrow(BusinessException::class, 'Only pending or processing queries can be cancelled.');
});