<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/** @mixin \App\Domains\Scanner\Models\ScanSession */
final class ScanSessionResource extends JsonResource {
    public function toArray(Request $r): array {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'device_id' => $this->device_id,
            'session_number' => $this->session_number,
            'scan_type' => $this->scan_type,
            'status' => $this->status,
            'notes' => $this->notes,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
