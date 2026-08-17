<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Policies;

use App\Domains\Reporting\Models\Reporting;
use App\Domains\User\Models\User;

final class ReportingPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Reporting $r): bool
    {
        return $u->organization_id === $r->organization_id;
    }

    public function update(User $u, Reporting $r): bool
    {
        return true;
    }

    public function delete(User $u, Reporting $r): bool
    {
        return true;
    }
}