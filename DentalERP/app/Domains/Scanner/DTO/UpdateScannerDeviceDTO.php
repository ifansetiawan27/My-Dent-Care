<?php
declare(strict_types=1);
namespace App\Domains\Scanner\DTO;
final readonly class UpdateScannerDeviceDTO {
    public function __construct(
        public ?string $deviceName = null,
        public ?string $model = null,
        public ?string $serialNumber = null,
        public ?string $manufacturer = null,
        public ?string $firmwareVersion = null,
        public ?string $status = null,
        public ?string $location = null,
        public ?string $purchaseDate = null,
        public ?string $warrantyExpiryDate = null,
    ) {}
    public function toArray(): array {
        return array_filter([
            'device_name' => $this->deviceName,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'manufacturer' => $this->manufacturer,
            'firmware_version' => $this->firmwareVersion,
            'status' => $this->status,
            'location' => $this->location,
            'purchase_date' => $this->purchaseDate,
            'warranty_expiry_date' => $this->warrantyExpiryDate,
        ], fn ($v) => $v !== null);
    }
}
