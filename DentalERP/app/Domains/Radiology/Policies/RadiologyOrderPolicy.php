<?php

declare(strict_types=1);

namespace App\Domains\Radiology\Policies;

use App\Domains\Radiology\Models\RadiologyOrder;
use App\Domains\User\Models\User;

final class RadiologyOrderPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, RadiologyOrder $order): bool
    {
        return $u->organization_id === $order->organization_id;
    }

    public function update(User $u, RadiologyOrder $order): bool
    {
        return $u->organization_id === $order->organization_id;
    }

    public function delete(User $u, RadiologyOrder $order): bool
    {
        return $u->organization_id === $order->organization_id;
    }
}
