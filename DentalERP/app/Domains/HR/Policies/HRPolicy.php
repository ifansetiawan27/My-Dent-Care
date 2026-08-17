<?php

declare(strict_types=1);

namespace App\Domains\HR\Policies;

use App\Domains\HR\Models\HR;
use App\Domains\User\Models\User;

final class HRPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, HR $h): bool
    {
        return $u->organization_id === $h->organization_id;
    }

    public function update(User $u, HR $h): bool
    {
        return true;
    }

    public function delete(User $u, HR $h): bool
    {
        return true;
    }
}