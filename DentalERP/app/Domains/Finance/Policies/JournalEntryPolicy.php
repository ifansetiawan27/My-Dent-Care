<?php

declare(strict_types=1);

namespace App\Domains\Finance\Policies;

use App\Domains\Finance\Models\JournalEntry;
use App\Domains\User\Models\User;

final class JournalEntryPolicy
{
    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, JournalEntry $je): bool
    {
        return $u->organization_id === $je->organization_id;
    }

    public function create(User $u): bool
    {
        return true;
    }

    public function update(User $u, JournalEntry $je): bool
    {
        return $u->organization_id === $je->organization_id;
    }

    public function delete(User $u, JournalEntry $je): bool
    {
        return $u->organization_id === $je->organization_id && $je->status === 'draft';
    }
}
