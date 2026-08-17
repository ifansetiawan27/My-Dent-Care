<?php

declare(strict_types=1);

namespace App\Domains\Dashboard\Policies;

use App\Domains\Dashboard\Models\Dashboard;
use App\Domains\User\Models\User;

final class DashboardPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Dashboard $d): bool
    {
        return $u->organization_id === $d->organization_id;
    }

    public function update(User $u, Dashboard $d): bool
    {
        return true;
    }

    public function delete(User $u, Dashboard $d): bool
    {
        return true;
    }
}