<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Interfaces;

use App\Domains\Appointment\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AppointmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): ?Appointment;
    public function create(array $data): Appointment;
    public function update(Appointment $appointment, array $data): Appointment;
    public function delete(Appointment $appointment): bool;
    public function existsByCode(string $code, ?string $excludeId = null): bool;
}