<?php
declare(strict_types=1);
namespace App\Domains\Scanner\DTO;
final readonly class CreateScanFileDTO {
    public function __construct(
        public string $scanSessionId,
        public string $filePath,
        public int $fileSize,
        public string $fileFormat,
        public ?bool $isPrimary = null,
        public ?string $processingStatus = null,
    ) {}
    public function toArray(): array {
        return [
            'scan_session_id' => $this->scanSessionId,
            'file_path' => $this->filePath,
            'file_size' => $this->fileSize,
            'file_format' => $this->fileFormat,
            'is_primary' => $this->isPrimary ?? false,
            'processing_status' => $this->processingStatus ?? 'pending',
        ];
    }
}
