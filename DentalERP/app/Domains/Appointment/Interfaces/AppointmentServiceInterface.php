<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Interfaces;

use App\Domains\Appointment\DTO\CreateAppointmentDTO;
use App\Domains\Appointment\DTO\UpdateAppointmentDTO;
use App\Domains\Appointment\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AppointmentServiceInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function findById(string $id, string $organizationId): Appointment;
    public function create(CreateAppointmentDTO $dto): Appointment;
    public function update(string $id, UpdateAppointmentDTO $dto, string $organizationId): Appointment;
    public function delete(string $id, string $organizationId): bool;
    public function toggleActive(string $id, string $organizationId): Appointment;
}