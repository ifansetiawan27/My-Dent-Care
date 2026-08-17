<?php

declare(strict_types=1);

namespace App\Domains\Billing\DTO;

final readonly class CreateBillingDTO
{
    public function __construct(
        public string $totalAmount,
        public string $organizationId,
        public ?string $patientId = null,
        public ?string $paidAmount = null,
        public ?string $dueDate = null,
        public ?array $items = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'patient_id'      => $this->patientId,
            'total_amount'    => $this->totalAmount,
            'paid_amount'     => $this->paidAmount ?? '0',
            'due_date'        => $this->dueDate,
            'items'           => $this->items,
            'notes'           => $this->notes,
            'organization_id' => $this->organizationId,
            'status'          => 'draft',
        ];
    }
}