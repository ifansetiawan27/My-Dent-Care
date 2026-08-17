<?php

declare(strict_types=1);

namespace App\Domains\Asset\DTO;

final readonly class CreateAssetDTO
{
    public function __construct(
        public string $assetCode,
        public string $name,
        public string $organizationId,
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
        return [
            'asset_code'      => $this->assetCode,
            'name'            => $this->name,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'category_id'     => $this->categoryId,
            'description'     => $this->description,
            'purchase_date'   => $this->purchaseDate,
            'purchase_price'  => $this->purchasePrice,
            'warranty_expiry' => $this->warrantyExpiry,
            'notes'           => $this->notes,
            'status'          => 'active',
        ];
    }
}