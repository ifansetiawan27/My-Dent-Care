<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Requests;
use App\Domains\Scanner\Enums\ScannerDeviceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class UpdateScannerDeviceRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'device_name' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|max:255',
            'serial_number' => ['sometimes', 'string', 'max:255', Rule::unique('scanner_devices', 'serial_number')->ignore($this->id)],
            'manufacturer' => 'sometimes|string|max:255',
            'firmware_version' => 'nullable|string|max:50',
            'status' => ['sometimes', 'string', Rule::in(ScannerDeviceStatus::values())],
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry_date' => 'nullable|date',
        ];
    }
}
