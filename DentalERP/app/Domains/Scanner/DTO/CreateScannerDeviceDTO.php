<?php
declare(strict_types=1);
namespace App\Domains\Scanner\DTO;
final readonly class CreateScannerDeviceDTO {
    public function __construct(
        public string $deviceName,
        public string $model,
        public string $serialNumber,
        public string $manufacturer,
        public ?string $firmwareVersion = null,
        public ?string $status = null,
        public ?string $location = null,
        public ?string $purchaseDate = null,
        public ?string $warrantyExpiryDate = null,
    ) {}
    public function toArray(): array {
        return [
            'device_name' => $this->deviceName,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'manufacturer' => $this->manufacturer,
            'firmware_version' => $this->firmwareVersion,
            'status' => $this->status ?? 'active',
            'location' => $this->location,
            'purchase_date' => $this->purchaseDate,
            'warranty_expiry_date' => $this->warrantyExpiryDate,
        ];
    }
}
