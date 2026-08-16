<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Appointment\DTO\CreateAppointmentDTO;
use App\Domains\Appointment\DTO\UpdateAppointmentDTO;
use App\Domains\Appointment\Interfaces\AppointmentRepositoryInterface;
use App\Domains\Appointment\Interfaces\AppointmentServiceInterface;
use App\Domains\Appointment\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class AppointmentService implements AppointmentServiceInterface
{
    public function __construct(
        private readonly AppointmentRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Appointment
    {
        $appointment = $this->repository->findById($id, $organizationId);
        if (! $appointment) {
            throw new NotFoundException('Appointment not found.');
        }
        return $appointment;
    }

    public function create(CreateAppointmentDTO $dto): Appointment
    {
        return DB::transaction(fn (): Appointment => $this->repository->create($dto->toArray()));
    }

    public function update(string $id, UpdateAppointmentDTO $dto, string $organizationId): Appointment
    {
        $appointment = $this->findById($id, $organizationId);
        return DB::transaction(fn (): Appointment => $this->repository->update($appointment, $dto->toArray()));
    }

    public function delete(string $id, string $organizationId): bool
    {
        return $this->repository->delete($this->findById($id, $organizationId));
    }

    public function toggleActive(string $id, string $organizationId): Appointment
    {
        $appointment = $this->findById($id, $organizationId);
        $newStatus = $appointment->status === 'cancelled' ? 'scheduled' : 'cancelled';
        return $this->repository->update($appointment, ['status' => $newStatus]);
    }
}