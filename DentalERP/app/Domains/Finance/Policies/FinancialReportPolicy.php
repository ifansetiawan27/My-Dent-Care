<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\FinancialReport;
use App\Domains\User\Models\User;

final class FinancialReportPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, FinancialReport $fr): bool
    {
        return $u->organization_id === $fr->organization_id;
    }

    public function create(User $u): bool
    {
        return true;
    }

    public function update(User $u, FinancialReport $fr): bool
    {
        return $u->organization_id === $fr->organization_id;
    }

    public function delete(User $u, FinancialReport $fr): bool
    {
        return $u->organization_id === $fr->organization_id;
    }
}
