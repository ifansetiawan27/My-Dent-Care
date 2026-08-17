<?php

declare(strict_types=1);

namespace App\Domains\Laboratory\Policies;

use App\Domains\Laboratory\Models\Laboratory;
use App\Domains\User\Models\User;

final class LaboratoryPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Laboratory $l): bool
    {
        return $u->organization_id === $l->organization_id;
    }

    public function update(User $u, Laboratory $l): bool
    {
        return true;
    }

    public function delete(User $u, Laboratory $l): bool
    {
        return true;
    }
}