<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\Policies;

use App\Domains\Pharmacy\Models\Pharmacy;
use App\Domains\User\Models\User;

final class PharmacyPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Pharmacy $p): bool
    {
        return $u->organization_id === $p->organization_id;
    }

    public function update(User $u, Pharmacy $p): bool
    {
        return true;
    }

    public function delete(User $u, Pharmacy $p): bool
    {
        return true;
    }
}