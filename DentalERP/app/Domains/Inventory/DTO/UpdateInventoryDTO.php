<?php

declare(strict_types=1);

namespace App\Domains\Inventory\DTO;

final readonly class UpdateInventoryDTO
{
    public function __construct(
        public ?string $itemCode = null,
        public ?string $name = null,
        public ?string $unit = null,
        public ?string $branchId = null,
        public ?string $categoryId = null,
        public ?string $description = null,
        public ?string $quantity = null,
        public ?string $minQuantity = null,
        public ?string $unitPrice = null,
        public ?bool $isActive = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'item_code'    => $this->itemCode,
            'name'         => $this->name,
            'unit'         => $this->unit,
            'branch_id'    => $this->branchId,
            'category_id'  => $this->categoryId,
            'description'  => $this->description,
            'quantity'     => $this->quantity,
            'min_quantity' => $this->minQuantity,
            'unit_price'   => $this->unitPrice,
            'is_active'    => $this->isActive,
        ], fn ($v) => $v !== null);
    }
}