<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Requests;
use App\Domains\Scanner\Enums\ScanFileFormat;
use App\Domains\Scanner\Enums\ScanFileProcessingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
final class StoreScanFileRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'scan_session_id' => 'required|uuid|exists:scan_sessions,id',
            'file_path' => 'required|string|max:500',
            'file_size' => 'required|integer|min:0',
            'file_format' => ['required', 'string', Rule::in(ScanFileFormat::values())],
            'is_primary' => 'sometimes|boolean',
            'processing_status' => ['sometimes', 'string', Rule::in(ScanFileProcessingStatus::values())],
        ];
    }
}
