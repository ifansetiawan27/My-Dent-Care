<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Branch;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\Interfaces\BranchServiceInterface;
use App\Domains\Branch\Models\Branch;
use App\Domains\Branch\Enums\BranchStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * BranchControllerTest
 *
 * Feature tests for BranchController REST API endpoints.
 * The BranchServiceInterface is mocked to isolate controller behavior.
 * Tests cover: HTTP status codes, response structure, validation, authorization.
 */
class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Mockery::mock(BranchServiceInterface::class);
        $this->app->instance(BranchServiceInterface::class, $this->service);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeBranch(array $attributes = []): Branch
    {
        $branch = new Branch();
        $branch->forceFill(array_merge([
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => (string) Str::orderedUuid(),
            'branch_code'     => 'BRC-0001',
            'branch_name'     => 'Test Clinic',
            'branch_type'     => 'clinic',
            'email'           => 'test@clinic.com',
            'phone'           => '+62-21-000000',
            'address'         => 'Jl. Test No. 1',
            'city'            => 'Jakarta',
            'province'        => 'DKI Jakarta',
            'country'         => 'Indonesia',
            'postal_code'     => '12345',
            'timezone'        => 'Asia/Jakarta',
            'status'          => BranchStatus::Active,
        ], $attributes));
        $branch->exists = true;

        return $branch;
    }

    private function makePaginator(array $items = [], int $total = 0): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items:       $items,
            total:       $total,
            perPage:     15,
            currentPage: 1,
        );
    }

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'organization_id' => (string) Str::orderedUuid(),
            'branch_code'     => 'BRC-0001',
            'branch_name'     => 'Test Clinic',
            'branch_type'     => 'clinic',
            'email'           => 'test@clinic.com',
            'phone'           => '+62-21-000000',
            'address'         => 'Jl. Test No. 1',
            'city'            => 'Jakarta',
            'province'        => 'DKI Jakarta',
            'country'         => 'Indonesia',
            'postal_code'     => '12345',
            'timezone'        => 'Asia/Jakarta',
            'status'          => 'active',
        ], $override);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/branches — index
    // -------------------------------------------------------------------------

    public function test_index_returns_200_with_paginated_branches(): void
    {
        $orgId  = (string) Str::orderedUuid();
        $branch = $this->makeBranch(['organization_id' => $orgId]);
        $pager  = $this->makePaginator([$branch], 1);

        $this->service->shouldReceive('getPaginated')->once()->andReturn($pager);

        $response = $this->getJson("/api/v1/branches?organization_id={$orgId}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data',
                     'meta' => [
                         'pagination' => [
                             'total', 'per_page', 'current_page',
                             'last_page', 'from', 'to',
                         ],
                     ],
                 ])
                 ->assertJsonPath('success', true);
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/branches');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/branches/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_200_with_branch_data(): void
    {
        $branch = $this->makeBranch();

        $this->service->shouldReceive('getByUuid')->once()->with($branch->id)->andReturn($branch);

        $response = $this->getJson("/api/v1/branches/{$branch->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id', 'organization_id', 'branch_code',
                         'branch_name', 'status', 'city',
                     ],
                 ])
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.id', $branch->id);
    }

    public function test_show_returns_404_when_branch_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->service
            ->shouldReceive('getByUuid')
            ->once()
            ->with($uuid)
            ->andThrow(new NotFoundException("Branch with ID [{$uuid}] not found."));

        $response = $this->getJson("/api/v1/branches/{$uuid}");

        $response->assertStatus(404)
                 ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/branches — store
    // -------------------------------------------------------------------------

    public function test_store_creates_branch_and_returns_201(): void
    {
        $branch = $this->makeBranch();

        $this->service->shouldReceive('create')->once()->andReturn($branch);

        $response = $this->postJson('/api/v1/branches', $this->validPayload());

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.branch_code', $branch->branch_code);
    }

    public function test_store_returns_422_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/branches', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    public function test_store_returns_422_when_organization_id_invalid(): void
    {
        $response = $this->postJson('/api/v1/branches', $this->validPayload([
            'organization_id' => 'not-a-uuid',
        ]));

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['organization_id']);
    }

    public function test_store_returns_422_when_status_invalid(): void
    {
        $response = $this->postJson('/api/v1/branches', $this->validPayload([
            'status' => 'invalid-status',
        ]));

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    public function test_store_returns_422_when_branch_code_has_special_characters(): void
    {
        $response = $this->postJson('/api/v1/branches', $this->validPayload([
            'branch_code' => 'BRC 0001!',  // space and special char not allowed
        ]));

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['branch_code']);
    }

    public function test_store_returns_422_from_service_business_rule(): void
    {
        $this->service
            ->shouldReceive('create')
            ->once()
            ->andThrow(new BusinessException('Branch code [BRC-0001] already exists in this organization.'));

        $response = $this->postJson('/api/v1/branches', $this->validPayload());

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/branches/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_returns_200_with_updated_branch(): void
    {
        $branch  = $this->makeBranch(['branch_name' => 'Updated Clinic']);

        $this->service->shouldReceive('update')->once()->andReturn($branch);

        $response = $this->putJson("/api/v1/branches/{$branch->id}", [
            'branch_name' => 'Updated Clinic',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.branch_name', 'Updated Clinic');
    }

    public function test_update_returns_404_when_branch_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->service
            ->shouldReceive('update')
            ->once()
            ->andThrow(new NotFoundException("Branch with ID [{$uuid}] not found."));

        $response = $this->putJson("/api/v1/branches/{$uuid}", [
            'branch_name' => 'New Name',
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/branches/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_returns_200_on_successful_delete(): void
    {
        $id = (string) Str::orderedUuid();

        $this->service->shouldReceive('delete')->once()->with($id)->andReturn(true);

        $response = $this->deleteJson("/api/v1/branches/{$id}");

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_destroy_returns_422_when_delete_guard_fails(): void
    {
        $id = (string) Str::orderedUuid();

        $this->service
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new BusinessException('Cannot delete branch. It still has Users.'));

        $response = $this->deleteJson("/api/v1/branches/{$id}");

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    public function test_destroy_returns_404_when_branch_not_found(): void
    {
        $id = (string) Str::orderedUuid();

        $this->service
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new NotFoundException("Branch with ID [{$id}] not found."));

        $response = $this->deleteJson("/api/v1/branches/{$id}");

        $response->assertStatus(404)
                 ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/branches/{id}/restore — restore
    // -------------------------------------------------------------------------

    public function test_restore_returns_200_on_success(): void
    {
        $id = (string) Str::orderedUuid();

        $this->service->shouldReceive('restore')->once()->with($id)->andReturn(true);

        $response = $this->postJson("/api/v1/branches/{$id}/restore");

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_restore_returns_404_when_branch_not_found(): void
    {
        $id = (string) Str::orderedUuid();

        $this->service
            ->shouldReceive('restore')
            ->once()
            ->andThrow(new NotFoundException("Branch with ID [{$id}] not found."));

        $response = $this->postJson("/api/v1/branches/{$id}/restore");

        $response->assertStatus(404)
                 ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // Authorization
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/branches');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/branches', $this->validPayload());
        $response->assertStatus(401);
    }
}
