<?php
declare(strict_types=1);
namespace App\Domains\Scanner\DTO;
final readonly class UpdateScanSessionDTO {
    public function __construct(
        public ?string $patientId = null,
        public ?string $doctorId = null,
        public ?string $deviceId = null,
        public ?string $scanType = null,
        public ?string $status = null,
        public ?string $notes = null,
    ) {}
    public function toArray(): array {
        return array_filter([
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'device_id' => $this->deviceId,
            'scan_type' => $this->scanType,
            'status' => $this->status,
            'notes' => $this->notes,
        ], fn ($v) => $v !== null);
    }
}
