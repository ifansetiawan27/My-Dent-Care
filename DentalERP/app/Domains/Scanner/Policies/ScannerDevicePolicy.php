<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Policies;
use App\Domains\Scanner\Models\ScannerDevice;
use App\Domains\User\Models\User;
final class ScannerDevicePolicy {
    public function viewAny(User $u): bool { return true; }
    public function view(User $u, ScannerDevice $d): bool { return true; }
    public function create(User $u): bool { return true; }
    public function update(User $u, ScannerDevice $d): bool { return true; }
    public function delete(User $u, ScannerDevice $d): bool { return true; }
}
