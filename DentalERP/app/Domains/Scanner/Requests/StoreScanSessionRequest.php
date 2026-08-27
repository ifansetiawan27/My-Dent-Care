<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Requests;
use App\Domains\Scanner\Enums\ScanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class StoreScanSessionRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'patient_id' => 'required|uuid|exists:patients,id',
            'doctor_id' => 'required|uuid|exists:users,id',
            'device_id' => 'required|uuid|exists:scanner_devices,id',
            'scan_type' => ['required', 'string', Rule::in(ScanType::values())],
            'notes' => 'nullable|string',
        ];
    }
}
