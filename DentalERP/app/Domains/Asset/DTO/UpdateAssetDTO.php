<?php

declare(strict_types=1);

namespace App\Domains\Asset\DTO;

final readonly class UpdateAssetDTO
{
    public function __construct(
        public ?string $assetCode = null,
        public ?string $name = null,
        public ?string $status = null,
        public ?string $branchId = null,
        public ?string $categoryId = null,
        public ?string $description = null,
        public ?string $purchaseDate = null,
        public ?string $purchasePrice = null,
        public ?string $warrantyExpiry = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'asset_code'      => $this->assetCode,
            'name'            => $this->name,
            'status'          => $this->status,
            'branch_id'       => $this->branchId,
            'category_id'     => $this->categoryId,
            'description'     => $this->description,
            'purchase_date'   => $this->purchaseDate,
            'purchase_price'  => $this->purchasePrice,
            'warranty_expiry' => $this->warrantyExpiry,
            'notes'           => $this->notes,
        ], fn ($v) => $v !== null);
    }
}