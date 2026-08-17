<?php

declare(strict_types=1);

namespace App\Domains\HR\DTO;

final readonly class CreateHRDTO
{
    public function __construct(
        public string $recordType,
        public string $effectiveDate,
        public string $organizationId,
        public ?string $employeeId = null,
        public ?string $endDate = null,
        public ?array $data = null,
        public ?string $notes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'record_type'     => $this->recordType,
            'effective_date'  => $this->effectiveDate,
            'organization_id' => $this->organizationId,
            'employee_id'     => $this->employeeId,
            'end_date'        => $this->endDate,
            'data'            => $this->data,
            'notes'           => $this->notes,
            'status'          => 'active',
        ];
    }
}