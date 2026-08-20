<?php

declare(strict_types=1);

namespace App\Domains\Organization\Factories;

use App\Domains\Organization\Enums\OrganizationStatus;
use App\Domains\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 *
 * Note: fakerphp/faker is not installed in this project, so values are
 * generated with Str::random()/random_int() instead of $this->faker/fake().
 */
class OrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $suffix = strtoupper(Str::random(10));

        return [
            'id'           => (string) Str::orderedUuid(),
            'company_code' => 'ORG-' . $suffix,
            'company_name' => 'Test Organization ' . $suffix,
            'legal_name'   => 'Test Organization ' . $suffix . ' Group',
            'tax_number'   => 'TAX-' . $suffix,
            'email'        => 'org-' . strtolower($suffix) . '@example.test',
            'phone'        => '+62-21-' . random_int(1000000, 9999999),
            'website'      => 'https://example.test',
            'address'      => 'Jl. Test No. ' . random_int(1, 999),
            'city'         => 'Jakarta',
            'province'     => 'DKI Jakarta',
            'country'      => 'Indonesia',
            'postal_code'  => (string) random_int(10000, 99999),
            'timezone'     => 'Asia/Jakarta',
            'currency'     => 'IDR',
            'status'       => OrganizationStatus::Active->value,
            'created_by'   => null,
            'updated_by'   => null,
        ];
    }

    /**
     * State: active organization.
     */
    public function active(): static
    {
        return $this->state(['status' => OrganizationStatus::Active->value]);
    }

    /**
     * State: inactive organization.
     */
    public function inactive(): static
    {
        return $this->state(['status' => OrganizationStatus::Inactive->value]);
    }
}
