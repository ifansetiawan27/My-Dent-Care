<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Branch\DTO;

use App\Domains\Branch\DTO\CreateBranchDTO;
use App\Domains\Branch\DTO\UpdateBranchDTO;
use App\Domains\Branch\Enums\BranchStatus;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

/**
 * CreateBranchDTOTest & UpdateBranchDTOTest
 *
 * Unit tests for Branch DTOs.
 * No framework bootstrapping needed — pure PHP unit tests.
 * Tests cover: immutability, toArray(), defaults, nullable handling.
 */
class CreateBranchDTOTest extends TestCase
{
    private string $orgId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orgId = (string) Str::orderedUuid();
    }

    private function makeDTO(array $override = []): CreateBranchDTO
    {
        return new CreateBranchDTO(
            organizationId: $override['organization_id'] ?? $this->orgId,
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
            status:         $override['status']          ?? BranchStatus::Active,
            email:          $override['email']           ?? null,
            latitude:       $override['latitude']        ?? null,
            longitude:      $override['longitude']       ?? null,
        );
    }

    // -------------------------------------------------------------------------
    // CreateBranchDTO tests
    // -------------------------------------------------------------------------

    public function test_toArray_contains_all_required_keys(): void
    {
        $dto = $this->makeDTO();

        $array = $dto->toArray();

        $this->assertArrayHasKey('organization_id', $array);
        $this->assertArrayHasKey('branch_code', $array);
        $this->assertArrayHasKey('branch_name', $array);
        $this->assertArrayHasKey('branch_type', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('phone', $array);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertArrayHasKey('province', $array);
        $this->assertArrayHasKey('country', $array);
        $this->assertArrayHasKey('postal_code', $array);
        $this->assertArrayHasKey('latitude', $array);
        $this->assertArrayHasKey('longitude', $array);
        $this->assertArrayHasKey('timezone', $array);
        $this->assertArrayHasKey('status', $array);
    }

    public function test_toArray_maps_values_correctly(): void
    {
        $dto   = $this->makeDTO(['branch_code' => 'BRC-9999', 'branch_name' => 'My Clinic']);
        $array = $dto->toArray();

        $this->assertSame($this->orgId, $array['organization_id']);
        $this->assertSame('BRC-9999', $array['branch_code']);
        $this->assertSame('My Clinic', $array['branch_name']);
        $this->assertSame('active', $array['status']); // enum value, not instance
    }

    public function test_status_defaults_to_active(): void
    {
        $dto = new CreateBranchDTO(
            organizationId: $this->orgId,
            branchCode:     'BRC-0001',
            branchName:     'Test',
            branchType:     'clinic',
            phone:          '+62-21-000000',
            address:        'Jl. Test',
            city:           'Jakarta',
            province:       'DKI Jakarta',
            country:        'Indonesia',
            postalCode:     '12345',
            timezone:       'Asia/Jakarta',
        );

        $this->assertSame(BranchStatus::Active, $dto->status);
        $this->assertSame('active', $dto->toArray()['status']);
    }

    public function test_nullable_fields_are_null_by_default(): void
    {
        $dto   = $this->makeDTO();
        $array = $dto->toArray();

        $this->assertNull($array['email']);
        $this->assertNull($array['latitude']);
        $this->assertNull($array['longitude']);
    }

    public function test_nullable_fields_are_included_when_provided(): void
    {
        $dto = $this->makeDTO([
            'email'     => 'clinic@test.com',
            'latitude'  => '-6.2088',
            'longitude' => '106.8456',
        ]);

        $array = $dto->toArray();

        $this->assertSame('clinic@test.com', $array['email']);
        $this->assertSame('-6.2088', $array['latitude']);
        $this->assertSame('106.8456', $array['longitude']);
    }

    public function test_dto_properties_are_readonly(): void
    {
        $dto = $this->makeDTO();

        $this->expectException(\Error::class);

        // @phpstan-ignore-next-line
        $dto->branchCode = 'MODIFIED';
    }

    // -------------------------------------------------------------------------
    // UpdateBranchDTO tests
    // -------------------------------------------------------------------------

    public function test_update_dto_toArray_excludes_null_values(): void
    {
        $dto   = new UpdateBranchDTO(branchName: 'Updated Name');
        $array = $dto->toArray();

        $this->assertArrayHasKey('branch_name', $array);
        $this->assertArrayNotHasKey('branch_code', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('status', $array);
    }

    public function test_update_dto_isEmpty_returns_true_when_no_fields_set(): void
    {
        $dto = new UpdateBranchDTO();

        $this->assertTrue($dto->isEmpty());
    }

    public function test_update_dto_isEmpty_returns_false_when_fields_set(): void
    {
        $dto = new UpdateBranchDTO(branchName: 'New Name');

        $this->assertFalse($dto->isEmpty());
    }

    public function test_update_dto_status_serialized_as_string_value(): void
    {
        $dto   = new UpdateBranchDTO(status: BranchStatus::Inactive);
        $array = $dto->toArray();

        $this->assertSame('inactive', $array['status']);
    }

    public function test_update_dto_all_fields_included_when_set(): void
    {
        $dto = new UpdateBranchDTO(
            branchCode: 'BRC-002',
            branchName: 'New Name',
            branchType: 'mobile',
            phone:      '+62-21-999',
            city:       'Surabaya',
            status:     BranchStatus::Inactive,
        );

        $array = $dto->toArray();

        $this->assertSame('BRC-002', $array['branch_code']);
        $this->assertSame('New Name', $array['branch_name']);
        $this->assertSame('mobile', $array['branch_type']);
        $this->assertSame('+62-21-999', $array['phone']);
        $this->assertSame('Surabaya', $array['city']);
        $this->assertSame('inactive', $array['status']);
    }
}
