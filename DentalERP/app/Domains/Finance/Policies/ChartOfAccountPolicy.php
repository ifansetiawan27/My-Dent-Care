<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\ChartOfAccount;
use App\Domains\User\Models\User;

final class ChartOfAccountPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, ChartOfAccount $coa): bool
    {
        return $u->organization_id === $coa->organization_id;
    }

    public function create(User $u): bool
    {
        return true;
    }

    public function update(User $u, ChartOfAccount $coa): bool
    {
        return $u->organization_id === $coa->organization_id;
    }

    public function delete(User $u, ChartOfAccount $coa): bool
    {
        return $u->organization_id === $coa->organization_id;
    }
}
