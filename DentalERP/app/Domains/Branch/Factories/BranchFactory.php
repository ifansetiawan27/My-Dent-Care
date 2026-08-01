<?php

declare(strict_types=1);

namespace App\Domains\Branch\Factories;

use App\Domains\Branch\Enums\BranchStatus;
use App\Domains\Branch\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => (string) Str::orderedUuid(),
            'branch_code'     => strtoupper($this->faker->unique()->bothify('BRC-####')),
            'branch_name'     => $this->faker->company() . ' Dental Clinic',
            'branch_type'     => $this->faker->randomElement(['clinic', 'mobile', 'hospital']),
            'email'           => $this->faker->unique()->safeEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'address'         => $this->faker->streetAddress(),
            'city'            => $this->faker->city(),
            'province'        => $this->faker->state(),
            'country'         => 'Indonesia',
            'postal_code'     => $this->faker->postcode(),
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
            'latitude'  => (string) $this->faker->latitude(-10, 6),
            'longitude' => (string) $this->faker->longitude(95, 141),
        ]);
    }

    /**
     * State: branch belongs to a specific organization.
     */
    public function forOrganization(string $organizationId): static
    {
        return $this->state(['organization_id' => $organizationId]);
    }
}
