<?php

declare(strict_types=1);

namespace App\Domains\Pharmacy\DTO;

final readonly class CreatePharmacyDTO
{
    public function __construct(
        public string $drugCode,
        public string $name,
        public string $organizationId,
        public ?string $branchId = null,
        public ?string $category = null,
        public ?string $quantity = null,
        public ?string $unit = null,
        public ?string $unitPrice = null,
        public ?string $expiryDate = null,
        public ?string $batchNumber = null,
    ) {}

    public function toArray(): array
    {
        return [
            'drug_code'       => $this->drugCode,
            'name'            => $this->name,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'category'        => $this->category,
            'quantity'        => $this->quantity ?? '0',
            'unit'            => $this->unit,
            'unit_price'      => $this->unitPrice,
            'expiry_date'     => $this->expiryDate,
            'batch_number'    => $this->batchNumber,
            'is_active'       => true,
        ];
    }
}