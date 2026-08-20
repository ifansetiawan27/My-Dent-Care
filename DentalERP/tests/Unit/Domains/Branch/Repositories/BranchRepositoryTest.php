<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Branch\Repositories;

use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\Models\Branch;
use App\Domains\Branch\Repositories\BranchRepository;
use App\Domains\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * BranchRepositoryTest
 *
 * Unit tests for BranchRepository.
 * Uses RefreshDatabase to work with a real in-memory/test database.
 * Tests cover: CRUD, multi-tenant scoping, search, delete guards.
 */
class BranchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BranchRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new BranchRepository(new Branch());
    }

    // -------------------------------------------------------------------------
    // findById()
    // -------------------------------------------------------------------------

    public function test_findById_returns_branch_when_exists(): void
    {
        $branch = Branch::factory()->create();

        $result = $this->repository->findById($branch->id);

        $this->assertNotNull($result);
        $this->assertSame($branch->id, $result->id);
    }

    public function test_findById_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById((string) Str::orderedUuid());

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // findByUuid()
    // -------------------------------------------------------------------------

    public function test_findByUuid_returns_branch_when_exists(): void
    {
        $branch = Branch::factory()->create();

        $result = $this->repository->findByUuid($branch->id);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertSame($branch->id, $result->id);
    }

    public function test_findByUuid_throws_NotFoundException_when_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Branch with ID [{$uuid}] not found.");

        $this->repository->findByUuid($uuid);
    }

    // -------------------------------------------------------------------------
    // findByOrganization()
    // -------------------------------------------------------------------------

    public function test_findByOrganization_returns_only_branches_of_given_org(): void
    {
        $orgId = (string) Str::orderedUuid();

        Branch::factory()->count(3)->forOrganization($orgId)->create();
        Branch::factory()->count(2)->create(); // other org

        $result = $this->repository->findByOrganization($orgId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
        $result->each(fn ($b) => $this->assertSame($orgId, $b->organization_id));
    }

    public function test_findByOrganization_returns_empty_collection_when_no_branches(): void
    {
        $orgId = (string) Str::orderedUuid();

        $result = $this->repository->findByOrganization($orgId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_persists_branch_to_database(): void
    {
        $orgId = Organization::factory()->create()->id;

        $data = [
            'organization_id' => $orgId,
            'branch_code'     => 'BRC-TEST',
            'branch_name'     => 'Test Branch',
            'branch_type'     => 'clinic',
            'phone'           => '+62-21-0000000',
            'address'         => 'Jl. Test No. 1',
            'city'            => 'Jakarta',
            'province'        => 'DKI Jakarta',
            'country'         => 'Indonesia',
            'postal_code'     => '12345',
            'timezone'        => 'Asia/Jakarta',
            'status'          => 'active',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertDatabaseHas('branches', [
            'organization_id' => $orgId,
            'branch_code'     => 'BRC-TEST',
        ]);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function test_update_modifies_branch_in_database(): void
    {
        $branch = Branch::factory()->create(['branch_name' => 'Old Name']);

        $result = $this->repository->update($branch->id, ['branch_name' => 'New Name']);

        $this->assertSame('New Name', $result->branch_name);
        $this->assertDatabaseHas('branches', [
            'id'          => $branch->id,
            'branch_name' => 'New Name',
        ]);
    }

    public function test_update_throws_NotFoundException_when_branch_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->expectException(NotFoundException::class);

        $this->repository->update($uuid, ['branch_name' => 'New Name']);
    }

    // -------------------------------------------------------------------------
    // delete()
    // -------------------------------------------------------------------------

    public function test_delete_soft_deletes_branch(): void
    {
        $branch = Branch::factory()->create();

        $result = $this->repository->delete($branch->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('branches', ['id' => $branch->id]);
    }

    public function test_delete_throws_NotFoundException_when_branch_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->expectException(NotFoundException::class);

        $this->repository->delete($uuid);
    }

    // -------------------------------------------------------------------------
    // restore()
    // -------------------------------------------------------------------------

    public function test_restore_recovers_soft_deleted_branch(): void
    {
        $branch = Branch::factory()->create();
        $branch->delete();

        $result = $this->repository->restore($branch->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'deleted_at' => null]);
    }

    // -------------------------------------------------------------------------
    // search()
    // -------------------------------------------------------------------------

    public function test_search_returns_matching_branches_within_org(): void
    {
        $orgId = (string) Str::orderedUuid();

        Branch::factory()->forOrganization($orgId)->create([
            'branch_name' => 'Jakarta Dental',
            'branch_code' => 'BRC-JKT',
        ]);
        Branch::factory()->forOrganization($orgId)->create([
            'branch_name' => 'Surabaya Dental',
            'branch_code' => 'BRC-SBY',
        ]);

        $result = $this->repository->search($orgId, 'Jakarta');

        $this->assertSame(1, $result->total());
        $this->assertSame('Jakarta Dental', $result->items()[0]->branch_name);
    }

    public function test_search_does_not_return_branches_from_other_orgs(): void
    {
        $orgId      = (string) Str::orderedUuid();
        $otherOrgId = (string) Str::orderedUuid();

        Branch::factory()->forOrganization($orgId)->create(['branch_name' => 'Jakarta Dental']);
        Branch::factory()->forOrganization($otherOrgId)->create(['branch_name' => 'Jakarta Branch']);

        $result = $this->repository->search($orgId, 'Jakarta');

        $this->assertSame(1, $result->total());
    }

    // -------------------------------------------------------------------------
    // existsByCode()
    // -------------------------------------------------------------------------

    public function test_existsByCode_returns_true_when_code_taken_in_org(): void
    {
        $orgId = (string) Str::orderedUuid();
        Branch::factory()->forOrganization($orgId)->create(['branch_code' => 'BRC-001']);

        $result = $this->repository->existsByCode($orgId, 'BRC-001');

        $this->assertTrue($result);
    }

    public function test_existsByCode_returns_false_when_code_belongs_to_different_org(): void
    {
        $orgId      = (string) Str::orderedUuid();
        $otherOrgId = (string) Str::orderedUuid();
        Branch::factory()->forOrganization($otherOrgId)->create(['branch_code' => 'BRC-001']);

        $result = $this->repository->existsByCode($orgId, 'BRC-001');

        $this->assertFalse($result);
    }

    public function test_existsByCode_excludes_given_id_for_update_check(): void
    {
        $orgId  = (string) Str::orderedUuid();
        $branch = Branch::factory()->forOrganization($orgId)->create(['branch_code' => 'BRC-001']);

        // Same ID excluded → should return false (branch is not a conflict with itself)
        $result = $this->repository->existsByCode($orgId, 'BRC-001', $branch->id);

        $this->assertFalse($result);
    }
}
