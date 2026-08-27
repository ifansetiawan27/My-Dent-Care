<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Policies;
use App\Domains\Scanner\Models\ScanFile;
use App\Domains\User\Models\User;
final class ScanFilePolicy {
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, ScanFile $f): bool { return true; }
    public function create(User $u): bool { return true; }
    public function update(User $u, ScanFile $f): bool { return true; }
    public function delete(User $u, ScanFile $f): bool { return true; }
}
