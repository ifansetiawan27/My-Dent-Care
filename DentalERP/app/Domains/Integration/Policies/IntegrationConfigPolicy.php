<?php

declare(strict_types=1);

namespace App\Domains\Integration\Policies;

use App\Domains\Integration\Models\IntegrationConfig;
use App\Domains\User\Models\User;

final class IntegrationConfigPolicy
{
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, IntegrationConfig $c): bool { return $u->organization_id === $c->organization_id; }
    public function update(User $u, IntegrationConfig $c): bool { return $u->organization_id === $c->organization_id; }
    public function delete(User $u, IntegrationConfig $c): bool { return $u->organization_id === $c->organization_id; }
}
