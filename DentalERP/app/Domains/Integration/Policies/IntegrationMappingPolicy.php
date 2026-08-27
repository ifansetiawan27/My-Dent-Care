<?php

declare(strict_types=1);

namespace App\Domains\Integration\Policies;

use App\Domains\Integration\Models\IntegrationMapping;
use App\Domains\User\Models\User;

final class IntegrationMappingPolicy
{
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, IntegrationMapping $m): bool { return $u->organization_id === $m->config?->organization_id; }
    public function update(User $u, IntegrationMapping $m): bool { return $u->organization_id === $m->config?->organization_id; }
    public function delete(User $u, IntegrationMapping $m): bool { return $u->organization_id === $m->config?->organization_id; }
}
