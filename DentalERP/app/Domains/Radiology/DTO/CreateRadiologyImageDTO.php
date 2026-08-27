<?php

declare(strict_types=1);

namespace App\Domains\Radiology\DTO;

final readonly class CreateRadiologyImageDTO
{
    public function __construct(
        public string $radiologyOrderId,
        public string $imageType,
        public string $filePath,
        public ?int $fileSize = null,
        public ?string $fileMime = null,
        public ?string $thumbnailPath = null,
        public ?string $uploadedBy = null,
    ) {}

    public function toArray(): array
    {
        return [
            'radiology_order_id' => $this->radiologyOrderId,
            'image_type'         => $this->imageType,
            'file_path'          => $this->filePath,
            'file_size'          => $this->fileSize,
            'file_mime'          => $this->fileMime,
            'thumbnail_path'     => $this->thumbnailPath,
            'uploaded_by'        => $this->uploadedBy,
        ];
    }
}
