<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Requests;
use App\Domains\Scanner\Enums\ScannerDeviceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class StoreScannerDeviceRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'device_name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:scanner_devices,serial_number',
            'manufacturer' => 'required|string|max:255',
            'firmware_version' => 'nullable|string|max:50',
            'status' => ['nullable', 'string', Rule::in(ScannerDeviceStatus::values())],
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry_date' => 'nullable|date',
        ];
    }
}
