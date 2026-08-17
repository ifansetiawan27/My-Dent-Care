<?php

declare(strict_types=1);

namespace App\Domains\Inventory\DTO;

final readonly class CreateInventoryDTO
{
    public function __construct(
        public string $itemCode,
        public string $name,
        public string $unit,
        public string $organizationId,
        public ?string $branchId = null,
        public ?string $categoryId = null,
        public ?string $description = null,
        public ?string $quantity = null,
        public ?string $minQuantity = null,
        public ?string $unitPrice = null,
    ) {}

    public function toArray(): array
    {
        return [
            'item_code'      => $this->itemCode,
            'name'           => $this->name,
            'unit'           => $this->unit,
            'organization_id' => $this->organizationId,
            'branch_id'      => $this->branchId,
            'category_id'    => $this->categoryId,
            'description'    => $this->description,
            'quantity'       => $this->quantity ?? '0',
            'min_quantity'   => $this->minQuantity ?? '0',
            'unit_price'     => $this->unitPrice,
            'is_active'      => true,
        ];
    }
}