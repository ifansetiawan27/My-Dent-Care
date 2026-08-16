<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Repositories;

use App\Domains\Appointment\Interfaces\AppointmentRepositoryInterface;
use App\Domains\Appointment\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Appointment::with(['patient', 'doctor'])->where('organization_id', $filters['organization_id']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }
        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['date_from'])) {
            $query->where('scheduled_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('scheduled_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('scheduled_at', 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 20), 100));
    }

    public function findById(string $id, string $organizationId): ?Appointment
    {
        return Appointment::with(['patient', 'doctor'])->where('id', $id)->where('organization_id', $organizationId)->first();
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $appointment->update($data);
        return $appointment->refresh()->load(['patient', 'doctor']);
    }

    public function delete(Appointment $appointment): bool
    {
        return (bool) $appointment->delete();
    }

    public function existsByCode(string $code, ?string $excludeId = null): bool
    {
        $query = Appointment::where('scheduled_at', $code);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}