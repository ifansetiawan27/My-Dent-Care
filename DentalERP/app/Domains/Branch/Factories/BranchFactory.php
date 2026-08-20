<?php

declare(strict_types=1);

namespace App\Domains\Branch\Factories;

use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Models\Branch;
use App\Domains\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 *
 * Note: fakerphp/faker is not installed in this project, so values are
 * generated with Str::random()/random_int() instead of $this->faker/fake().
 */
class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * A fresh Organization is created for every branch so the
     * organization_id foreign key is always satisfied. Use forOrganization()
     * to attach the branch to a specific (existing or generated) organization.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $suffix = strtoupper(Str::random(8));

        return [
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => Organization::factory(),
            'branch_code'     => 'BRC-' . $suffix,
            'branch_name'     => 'Test Branch ' . $suffix,
            'branch_type'     => 'clinic',
            'email'           => 'branch-' . strtolower($suffix) . '@example.test',
            'phone'           => '+62-21-' . random_int(1000000, 9999999),
            'address'         => 'Jl. Test No. ' . random_int(1, 999),
            'city'            => 'City-' . strtoupper(Str::random(6)),
            'province'        => 'Province-' . strtoupper(Str::random(4)),
            'country'         => 'Indonesia',
            'postal_code'     => (string) random_int(10000, 99999),
            'latitude'        => null,
            'longitude'       => null,
            'timezone'        => 'Asia/Jakarta',
            'status'          => BranchStatus::Active->value,
            'created_by'      => null,
            'updated_by'      => null,
        ];
    }

    // -------------------------------------------------------------------------
    // States
    // -------------------------------------------------------------------------

    /**
     * State: active branch.
     */
    public function active(): static
    {
        return $this->state(['status' => BranchStatus::Active->value]);
    }

    /**
     * State: inactive branch.
     */
    public function inactive(): static
    {
        return $this->state(['status' => BranchStatus::Inactive->value]);
    }

    /**
     * State: branch with geolocation.
     */
    public function withLocation(): static
    {
        return $this->state([
            'latitude'  => (string) (random_int(-1000, 600) / 100),
            'longitude' => (string) (random_int(9500, 14100) / 100),
        ]);
    }

    /**
     * State: branch belongs to a specific organization.
     *
     * Ensures the target Organization row exists (creating it with the given
     * id when missing) so the organization_id foreign key is satisfied even
     * when tests pass an arbitrary UUID.
     */
    public function forOrganization(string $organizationId): static
    {
        if (! Organization::query()->whereKey($organizationId)->exists()) {
            Organization::factory()->create(['id' => $organizationId]);
        }

        return $this->state(['organization_id' => $organizationId]);
    }
}
