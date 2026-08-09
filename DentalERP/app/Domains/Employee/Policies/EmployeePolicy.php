<?php
declare(strict_types=1);
namespace App\Domains\Employee\Policies;
use App\Domains\User\Models\User; use App\Domains\Employee\Models\Employee;
final class EmployeePolicy { public function viewAny(User $u): bool { return true; } public function view(User $u, Employee $e): bool { return $u->organization_id === $e->organization_id; } public function create(User $u): bool { return $u->hasRole(['Super Admin','Owner']); } public function update(User $u, Employee $e): bool { return $u->hasRole(['Super Admin','Owner']) && $u->organization_id === $e->organization_id; } public function delete(User $u, Employee $e): bool { return $u->hasRole(['Super Admin','Owner']) && $u->organization_id === $e->organization_id; } }
