<?php

declare(strict_types=1);

namespace App\Domains\User\Factories;

use App\Domains\User\Models\User;
use App\Domains\User\Enums\UserStatus;
use App\Domains\User\Enums\UserGender;
use App\Domains\Organization\Models\Organization;
use App\Domains\Branch\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $suffix = strtoupper(Str::random(8));
        $org = Organization::factory()->create();
        $branch = Branch::factory()->forOrganization($org->id)->create();

        return [
            'id'              => (string) Str::orderedUuid(),
            'organization_id' => $org->id,
            'branch_id'       => $branch->id,
            'employee_code'   => 'EMP-' . $suffix,
            'name'            => 'Test User ' . $suffix,
            'username'        => 'user-' . strtolower($suffix),
            'email'           => 'user-' . strtolower($suffix) . '@example.test',
            'password'        => bcrypt('password'),
            'status'          => UserStatus::Active->value,
            'gender'          => UserGender::Male->value,
            'email_verified_at' => now(),
        ];
    }

    /**
     * State: user with specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(['email' => $email]);
    }

    /**
     * State: user with specific password.
     */
    public function withPassword(string $password): static
    {
        return $this->state(['password' => bcrypt($password)]);
    }

    /**
     * State: user belongs to specific organization.
     */
    public function forOrg(Organization $org): static
    {
        $branch = Branch::factory()->forOrganization($org->id)->create();
        return $this->state([
            'organization_id' => $org->id,
            'branch_id' => $branch->id,
        ]);
    }
}
