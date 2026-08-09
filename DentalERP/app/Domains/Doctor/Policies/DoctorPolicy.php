<?php
declare(strict_types=1);
namespace App\Domains\Doctor\Policies;
use App\Domains\User\Models\User; use App\Domains\Doctor\Models\Doctor;
final class DoctorPolicy { public function viewAny(User $u): bool { return true; } public function view(User $u, Doctor $d): bool { return $u->organization_id === $d->organization_id; } public function create(User $u): bool { return $u->hasRole(['Super Admin','Owner']); } public function update(User $u, Doctor $d): bool { return $u->hasRole(['Super Admin','Owner']) && $u->organization_id === $d->organization_id; } public function delete(User $u, Doctor $d): bool { return $u->hasRole(['Super Admin','Owner']) && $u->organization_id === $d->organization_id; } }
