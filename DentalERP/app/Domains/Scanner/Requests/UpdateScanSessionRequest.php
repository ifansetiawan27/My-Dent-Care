<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Requests;
use App\Domains\Scanner\Enums\ScanSessionStatus;
use App\Domains\Scanner\Enums\ScanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class UpdateScanSessionRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'patient_id' => 'sometimes|uuid|exists:patients,id',
            'doctor_id' => 'sometimes|uuid|exists:users,id',
            'device_id' => 'sometimes|uuid|exists:scanner_devices,id',
            'scan_type' => ['sometimes', 'string', Rule::in(ScanType::values())],
            'status' => ['sometimes', 'string', Rule::in(ScanSessionStatus::values())],
            'notes' => 'nullable|string',
        ];
    }
}
