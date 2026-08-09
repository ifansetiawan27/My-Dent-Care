<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Policies;

use App\Domains\User\Models\User;
use App\Domains\MasterData\Models\BaseMasterDataModel;

final class MasterDataPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, BaseMasterDataModel $model): bool { return true; }
    public function create(User $user): bool { return $user->hasRole(['Super Admin', 'Owner']); }
    public function update(User $user, BaseMasterDataModel $model): bool { return $user->hasRole(['Super Admin', 'Owner']); }
    public function delete(User $user, BaseMasterDataModel $model): bool { return $user->hasRole(['Super Admin', 'Owner']); }
}
