<?php

declare(strict_types=1);

namespace App\Domains\Integration\Policies;

use App\Domains\Integration\Models\IntegrationLog;
use App\Domains\User\Models\User;

final class IntegrationLogPolicy
{
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, IntegrationLog $l): bool { return $u->organization_id === $l->config?->organization_id; }
}
