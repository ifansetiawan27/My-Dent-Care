<?php

declare(strict_types=1);

namespace App\Domains\Billing\Services;

use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\NotFoundException;
use App\Domains\Billing\DTO\CreateBillingDTO;
use App\Domains\Billing\DTO\UpdateBillingDTO;
use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Interfaces\BillingRepositoryInterface;
use App\Domains\Billing\Interfaces\BillingServiceInterface;
use App\Domains\Billing\Models\Billing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class BillingService implements BillingServiceInterface
{
    public function __construct(
        private readonly BillingRepositoryInterface $repository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findById(string $id, string $organizationId): Billing
    {
        $billing = $this->repository->findById($id, $organizationId);
        if (! $billing) {
            throw new NotFoundException('Invoice not found.');
        }
        return $billing;
    }

    public function create(CreateBillingDTO $dto): Billing
    {
        $data = $dto->toArray();
        $data['invoice_number'] = $this->generateInvoiceNumber();

        return DB::transaction(fn (): Billing => $this->repository->create($data));
    }

    public function update(string $id, UpdateBillingDTO $dto, string $organizationId): Billing
    {
        $billing = $this->findById($id, $organizationId);
        $data = $dto->toArray();

        if (isset($data['status'])) {
            $this->validateStatusTransition(
                InvoiceStatus::from($billing->status),
                InvoiceStatus::from($data['status']),
            );
        }

        if (isset($data['paid_amount']) && isset($data['total_amount'])) {
            $this->validatePaidAmount($data['paid_amount'], $data['total_amount']);
        } elseif (isset($data['paid_amount'])) {
            $this->validatePaidAmount($data['paid_amount'], $billing->total_amount);
        }

        if (isset($data['paid_amount']) && isset($data['total_amount'])) {
            if (floatval((string) $data['paid_amount']) === floatval((string) $data['total_amount'])) {
                $data['status'] = InvoiceStatus::Paid->value;
            }
        }

        return DB::transaction(fn (): Billing => $this->repository->update($billing, $data));
    }

    public function delete(string $id, string $organizationId): bool
    {
        $billing = $this->findById($id, $organizationId);

        if (InvoiceStatus::from($billing->status)->isTerminal()) {
            throw new BusinessException('Cannot delete a paid or cancelled invoice.');
        }

        return $this->repository->delete($billing);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = Billing::where('invoice_number', 'LIKE', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $seq = $last ? (int) substr($last->invoice_number, -5) + 1 : 1;

        return $prefix . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    private function validateStatusTransition(InvoiceStatus $current, InvoiceStatus $new): void
    {
        if ($current === $new) {
            return;
        }

        if ($current->isTerminal()) {
            throw new BusinessException(
                "Cannot update an invoice that is already in '{$current->value}' status.",
            );
        }

        $allowed = match ($current) {
            InvoiceStatus::Draft => [InvoiceStatus::Sent, InvoiceStatus::Cancelled],
            InvoiceStatus::Sent => [InvoiceStatus::Paid, InvoiceStatus::Overdue],
            InvoiceStatus::Overdue => [InvoiceStatus::Paid, InvoiceStatus::Cancelled],
            default => [],
        };

        if (! in_array($new, $allowed, true)) {
            throw new BusinessException(
                "Cannot transition invoice from '{$current->value}' to '{$new->value}'.",
            );
        }
    }

    private function validatePaidAmount(string $paidAmount, string $totalAmount): void
    {
        if (floatval($paidAmount) < 0) {
            throw new BusinessException('Paid amount cannot be negative.');
        }

        if (floatval($paidAmount) > floatval($totalAmount)) {
            throw new BusinessException('Paid amount cannot exceed total amount.');
        }
    }
}