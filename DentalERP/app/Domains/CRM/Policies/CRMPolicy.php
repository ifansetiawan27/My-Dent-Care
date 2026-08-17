<?php

declare(strict_types=1);

namespace App\Domains\CRM\Policies;

use App\Domains\CRM\Models\CRM;
use App\Domains\User\Models\User;

final class CRMPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, CRM $c): bool
    {
        return $u->organization_id === $c->organization_id;
    }

    public function update(User $u, CRM $c): bool
    {
        return true;
    }

    public function delete(User $u, CRM $c): bool
    {
        return true;
    }
}