<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/** @mixin \App\Domains\Scanner\Models\ScannerDevice */
final class ScannerDeviceResource extends JsonResource {
    public function toArray(Request $r): array {
        return [
            'id' => $this->id,
            'device_name' => $this->device_name,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'firmware_version' => $this->firmware_version,
            'status' => $this->status,
            'location' => $this->location,
            'last_calibration_at' => $this->last_calibration_at?->toISOString(),
            'purchase_date' => $this->purchase_date?->toDateString(),
            'warranty_expiry_date' => $this->warranty_expiry_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
