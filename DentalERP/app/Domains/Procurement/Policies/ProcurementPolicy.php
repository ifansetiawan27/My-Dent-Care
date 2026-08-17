<?php

declare(strict_types=1);

namespace App\Domains\Procurement\Policies;

use App\Domains\Procurement\Models\Procurement;
use App\Domains\User\Models\User;

final class ProcurementPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Procurement $p): bool
    {
        return $u->organization_id === $p->organization_id;
    }

    public function update(User $u, Procurement $p): bool
    {
        return true;
    }

    public function delete(User $u, Procurement $p): bool
    {
        return true;
    }
}