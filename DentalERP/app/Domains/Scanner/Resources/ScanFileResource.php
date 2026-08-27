<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/** @mixin \App\Domains\Scanner\Models\ScanFile */
final class ScanFileResource extends JsonResource {
    public function toArray(Request $r): array {
        return [
            'id' => $this->id,
            'scan_session_id' => $this->scan_session_id,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'file_format' => $this->file_format,
            'is_primary' => $this->is_primary,
            'processing_status' => $this->processing_status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
