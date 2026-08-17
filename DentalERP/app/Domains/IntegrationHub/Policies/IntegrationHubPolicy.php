<?php

declare(strict_types=1);

namespace App\Domains\IntegrationHub\Policies;

use App\Domains\IntegrationHub\Models\IntegrationHub;
use App\Domains\User\Models\User;

final class IntegrationHubPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function view(User $user, IntegrationHub $integration): bool
    {
        return $user->hasRole('super_admin') || $user->organization_id === $integration->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('admin');
    }

    public function update(User $user, IntegrationHub $integration): bool
    {
        return $user->hasRole('super_admin') || $user->organization_id === $integration->organization_id;
    }

    public function delete(User $user, IntegrationHub $integration): bool
    {
        return $user->hasRole('super_admin') || $user->organization_id === $integration->organization_id;
    }
}