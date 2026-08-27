<?php
declare(strict_types=1);
namespace App\Domains\Scanner\DTO;
final readonly class CreateScanSessionDTO {
    public function __construct(
        public string $patientId,
        public string $doctorId,
        public string $deviceId,
        public string $scanType,
        public ?string $notes = null,
    ) {}
    public function toArray(): array {
        return [
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'device_id' => $this->deviceId,
            'scan_type' => $this->scanType,
            'notes' => $this->notes,
            'status' => 'in_progress',
        ];
    }
}
