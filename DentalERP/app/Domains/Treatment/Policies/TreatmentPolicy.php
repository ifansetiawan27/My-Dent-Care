<?php

declare(strict_types=1);

namespace App\Domains\Treatment\Policies;

use App\Domains\Treatment\Models\Treatment;
use App\Domains\User\Models\User;

final class TreatmentPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Treatment $t): bool
    {
        return $u->organization_id === $t->organization_id;
    }

    public function update(User $u, Treatment $t): bool
    {
        return true;
    }

    public function delete(User $u, Treatment $t): bool
    {
        return true;
    }
}