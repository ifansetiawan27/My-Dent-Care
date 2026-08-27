<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Policies;

use App\Domains\Radiology\Models\RadiologyImage;
use App\Domains\User\Models\User;

final class RadiologyImagePolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, RadiologyImage $image): bool
    {
        return $u->organization_id === $image->radiologyOrder?->organization_id;
    }

    public function update(User $u, RadiologyImage $image): bool
    {
        return $u->organization_id === $image->radiologyOrder?->organization_id;
    }

    public function delete(User $u, RadiologyImage $image): bool
    {
        return $u->organization_id === $image->radiologyOrder?->organization_id;
    }
}
