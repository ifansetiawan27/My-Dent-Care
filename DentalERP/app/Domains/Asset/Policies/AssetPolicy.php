<?php

declare(strict_types=1);

namespace App\Domains\Asset\Policies;

use App\Domains\Asset\Models\Asset;
use App\Domains\User\Models\User;

final class AssetPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Asset $a): bool
    {
        return $u->organization_id === $a->organization_id;
    }

    public function update(User $u, Asset $a): bool
    {
        return true;
    }

    public function delete(User $u, Asset $a): bool
    {
        return true;
    }
}