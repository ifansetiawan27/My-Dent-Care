<?php

declare(strict_types=1);

namespace App\Domains\Billing\Policies;

use App\Domains\Billing\Models\Billing;
use App\Domains\User\Models\User;

final class BillingPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Billing $b): bool
    {
        return $u->organization_id === $b->organization_id;
    }

    public function update(User $u, Billing $b): bool
    {
        return true;
    }

    public function delete(User $u, Billing $b): bool
    {
        return true;
    }
}