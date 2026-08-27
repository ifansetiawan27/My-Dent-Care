<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Policies;
use App\Domains\Scanner\Models\ScanSession;
use App\Domains\User\Models\User;
final class ScanSessionPolicy {
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, ScanSession $s): bool { return true; }
    public function create(User $u): bool { return true; }
    public function update(User $u, ScanSession $s): bool { return true; }
    public function delete(User $u, ScanSession $s): bool { return true; }
}
