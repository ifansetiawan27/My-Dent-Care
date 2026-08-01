<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Branch\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Branch\DTO\CreateBranchDTO;
use App\Domains\Branch\DTO\UpdateBranchDTO;
use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Factories\BranchFactory;
use App\Domains\Branch\Interfaces\BranchRepositoryInterface;
use App\Domains\Branch\Models\Branch;
use App\Domains\Branch\Services\BranchService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * BranchServiceTest
 *
 * Unit tests for BranchService.
 * All repository interactions are mocked.
 * Tests cover: CRUD, business rules, delete guards.
 */
class BranchServiceTest extends TestCase
{
    private BranchService $service;
    private MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(BranchRepositoryInterface::class);
        $this->service    = new BranchService($this->repository);
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

        return $branch;
    }

    private function makeCreateDTO(array $override = []): CreateBranchDTO
    {
        return new CreateBranchDTO(
            organizationId: $override['organization_id'] ?? (string) Str::orderedUuid(),
            branchCode:     $override['branch_code']     ?? 'BRC-0001',
            branchName:     $override['branch_name']     ?? 'Test Clinic',
            branchType:     $override['branch_type']     ?? 'clinic',
            phone:          $override['phone']           ?? '+62-21-000000',
            address:        $override['address']         ?? 'Jl. Test No. 1',
            city:           $override['city']            ?? 'Jakarta',
            province:       $override['province']        ?? 'DKI Jakarta',
            country:        $override['country']         ?? 'Indonesia',
            postalCode:     $override['postal_code']     ?? '12345',
            timezone:       $override['timezone']        ?? 'Asia/Jakarta',
        );
    }

    // -------------------------------------------------------------------------
    // getByUuid()
    // -------------------------------------------------------------------------

    public function test_getByUuid_returns_branch_when_found(): void
    {
        $branch = $this->makeBranch();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($branch->id)
            ->andReturn($branch);

        $result = $this->service->getByUuid($branch->id);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertSame($branch->id, $result->id);
    }

    public function test_getByUuid_throws_NotFoundException_when_not_found(): void
    {
        $uuid = (string) Str::orderedUuid();

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andThrow(new NotFoundException("Branch with ID [{$uuid}] not found."));

        $this->expectException(NotFoundException::class);

        $this->service->getByUuid($uuid);
    }

    // -------------------------------------------------------------------------
    // getByOrganization()
    // -------------------------------------------------------------------------

    public function test_getByOrganization_returns_collection_scoped_to_org(): void
    {
        $orgId    = (string) Str::orderedUuid();
        $branches = new Collection([$this->makeBranch(['organization_id' => $orgId])]);

        $this->repository
            ->shouldReceive('findByOrganization')
            ->once()
            ->with($orgId)
            ->andReturn($branches);

        $result = $this->service->getByOrganization($orgId);

        $this->assertCount(1, $result);
        $this->assertSame($orgId, $result->first()->organization_id);
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_succeeds_when_branch_code_is_unique(): void
    {
        $orgId  = (string) Str::orderedUuid();
        $dto    = $this->makeCreateDTO(['organization_id' => $orgId, 'branch_code' => 'BRC-0001']);
        $branch = $this->makeBranch(['organization_id' => $orgId, 'branch_code' => 'BRC-0001']);

        // No existing branches → code is free
        $this->repository
            ->shouldReceive('findByOrganization')
            ->once()
            ->with($orgId)
            ->andReturn(new Collection());

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($dto->toArray())
            ->andReturn($branch);

        $result = $this->service->create($dto);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertSame('BRC-0001', $result->branch_code);
    }

    public function test_create_throws_BusinessException_when_branch_code_already_exists(): void
    {
        $orgId   = (string) Str::orderedUuid();
        $dto     = $this->makeCreateDTO(['organization_id' => $orgId, 'branch_code' => 'BRC-0001']);
        $existing = $this->makeBranch(['organization_id' => $orgId, 'branch_code' => 'BRC-0001']);

        $this->repository
            ->shouldReceive('findByOrganization')
            ->once()
            ->with($orgId)
            ->andReturn(new Collection([$existing]));

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Branch code [BRC-0001] already exists in this organization.');

        $this->service->create($dto);
    }

    // -------------------------------------------------------------------------
    // update()
    // -------------------------------------------------------------------------

    public function test_update_succeeds_when_branch_code_unchanged(): void
    {
        $branch = $this->makeBranch(['branch_code' => 'BRC-0001']);
        $dto    = new UpdateBranchDTO(branchName: 'Updated Clinic');

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($branch->id)
            ->andReturn($branch);

        $updated = $this->makeBranch(['id' => $branch->id, 'branch_name' => 'Updated Clinic']);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($branch->id, $dto->toArray())
            ->andReturn($updated);

        $result = $this->service->update($branch->id, $dto);

        $this->assertSame('Updated Clinic', $result->branch_name);
    }

    public function test_update_throws_BusinessException_when_new_branch_code_already_taken(): void
    {
        $orgId   = (string) Str::orderedUuid();
        $branch  = $this->makeBranch(['organization_id' => $orgId, 'branch_code' => 'BRC-0001']);
        $other   = $this->makeBranch(['organization_id' => $orgId, 'branch_code' => 'BRC-0002']);
        $dto     = new UpdateBranchDTO(branchCode: 'BRC-0002');

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($branch->id)
            ->andReturn($branch);

        $this->repository
            ->shouldReceive('findByOrganization')
            ->once()
            ->with($orgId)
            ->andReturn(new Collection([$branch, $other]));

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Branch code [BRC-0002] already exists in this organization.');

        $this->service->update($branch->id, $dto);
    }

    public function test_update_throws_NotFoundException_when_branch_does_not_exist(): void
    {
        $uuid = (string) Str::orderedUuid();
        $dto  = new UpdateBranchDTO(branchName: 'New Name');

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andThrow(new NotFoundException("Branch with ID [{$uuid}] not found."));

        $this->expectException(NotFoundException::class);

        $this->service->update($uuid, $dto);
    }

    // -------------------------------------------------------------------------
    // delete() — Business Rules (Delete Guards)
    // -------------------------------------------------------------------------

    public function test_delete_succeeds_when_no_related_records(): void
    {
        $branch = $this->makeBranch();

        $this->repository->shouldReceive('findByUuid')->once()->with($branch->id)->andReturn($branch);
        $this->repository->shouldReceive('hasUsers')->once()->with($branch->id)->andReturn(false);
        $this->repository->shouldReceive('hasPatients')->once()->with($branch->id)->andReturn(false);
        $this->repository->shouldReceive('hasAppointments')->once()->with($branch->id)->andReturn(false);
        $this->repository->shouldReceive('hasInventories')->once()->with($branch->id)->andReturn(false);
        $this->repository->shouldReceive('hasFinanceTransactions')->once()->with($branch->id)->andReturn(false);
        $this->repository->shouldReceive('delete')->once()->with($branch->id)->andReturn(true);

        $result = $this->service->delete($branch->id);

        $this->assertTrue($result);
    }

    public function test_delete_throws_BusinessException_when_has_users(): void
    {
        $this->assertDeleteGuardFails('hasUsers', 'Users');
    }

    public function test_delete_throws_BusinessException_when_has_patients(): void
    {
        $this->assertDeleteGuardFails('hasPatients', 'Patients');
    }

    public function test_delete_throws_BusinessException_when_has_appointments(): void
    {
        $this->assertDeleteGuardFails('hasAppointments', 'Appointments');
    }

    public function test_delete_throws_BusinessException_when_has_inventories(): void
    {
        $this->assertDeleteGuardFails('hasInventories', 'Inventory');
    }

    public function test_delete_throws_BusinessException_when_has_finance_transactions(): void
    {
        $this->assertDeleteGuardFails('hasFinanceTransactions', 'Finance Transactions');
    }

    /**
     * Helper: assert delete throws BusinessException when a guard returns true.
     */
    private function assertDeleteGuardFails(string $guardMethod, string $label): void
    {
        $branch = $this->makeBranch();

        $this->repository->shouldReceive('findByUuid')->once()->with($branch->id)->andReturn($branch);
        $this->repository->shouldReceive($guardMethod)->once()->with($branch->id)->andReturn(true);

        // Other guards should not be called after first failure
        $this->repository->shouldReceive('hasUsers')->atMost()->once()->andReturn(false);
        $this->repository->shouldReceive('hasPatients')->atMost()->once()->andReturn(false);
        $this->repository->shouldReceive('hasAppointments')->atMost()->once()->andReturn(false);
        $this->repository->shouldReceive('hasInventories')->atMost()->once()->andReturn(false);
        $this->repository->shouldReceive('hasFinanceTransactions')->atMost()->once()->andReturn(false);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage("Cannot delete branch. It still has {$label}.");

        $this->service->delete($branch->id);
    }

    // -------------------------------------------------------------------------
    // restore()
    // -------------------------------------------------------------------------

    public function test_restore_succeeds_for_soft_deleted_branch(): void
    {
        $id = (string) Str::orderedUuid();

        $this->repository
            ->shouldReceive('restore')
            ->once()
            ->with($id)
            ->andReturn(true);

        $result = $this->service->restore($id);

        $this->assertTrue($result);
    }

    public function test_restore_throws_NotFoundException_when_branch_not_found(): void
    {
        $id = (string) Str::orderedUuid();

        $this->repository
            ->shouldReceive('restore')
            ->once()
            ->with($id)
            ->andThrow(new NotFoundException("Branch with ID [{$id}] not found."));

        $this->expectException(NotFoundException::class);

        $this->service->restore($id);
    }
}
