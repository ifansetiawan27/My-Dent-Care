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
            $isFullyPaid = floatval((string) $data['paid_amount']) === floatval((string) $data['total_amount']);
            $currentStatus = InvoiceStatus::from($billing->status);

            // Only auto-settle a live invoice. A cancelled/void invoice must not
            // be flipped back to Paid just because a paid_amount was sent, and
            // an already-Paid invoice stays as-is (no state machine violation).
            if ($isFullyPaid && $currentStatus !== InvoiceStatus::Paid && ! $currentStatus->isTerminal()) {
                $data['status'] = InvoiceStatus::Paid->value;
            }
        }

        return DB::transaction(fn (): Billing => $this->repository->update($billing, $data));
    }

    /**
     * Record a payment against an invoice (front-desk cashier workflow).
     *
     * The amount is added to any existing payment; a fully settled invoice is
     * transitioned to Paid through the state machine, never by clobbering a
     * terminal status.
     */
    public function recordPayment(string $id, float $amount, string $organizationId): Billing
    {
        $billing = $this->findById($id, $organizationId);

        $total = floatval((string) $billing->total_amount);
        $newPaid = floatval((string) $billing->paid_amount) + $amount;

        if ($amount <= 0.0) {
            throw new BusinessException('Payment amount must be greater than zero.');
        }

        if ($newPaid > $total) {
            throw new BusinessException('Payment exceeds the outstanding balance.');
        }

        $currentStatus = InvoiceStatus::from($billing->status);
        if ($currentStatus->isTerminal()) {
            throw new BusinessException("Cannot record a payment on an invoice in '{$currentStatus->value}' status.");
        }

        $data = ['paid_amount' => $newPaid];

        if ($total > 0.0 && $newPaid === $total) {
            if ($currentStatus !== InvoiceStatus::Paid) {
                $this->validateStatusTransition($currentStatus, InvoiceStatus::Paid);
            }
            $data['status'] = InvoiceStatus::Paid->value;
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