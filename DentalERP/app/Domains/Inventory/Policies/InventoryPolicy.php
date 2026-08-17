<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Policies;

use App\Domains\Inventory\Models\Inventory;
use App\Domains\User\Models\User;

final class InventoryPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Inventory $i): bool
    {
        return $u->organization_id === $i->organization_id;
    }

    public function update(User $u, Inventory $i): bool
    {
        return true;
    }

    public function delete(User $u, Inventory $i): bool
    {
        return true;
    }
}