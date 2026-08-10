<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Services;
use App\Platform\PaymentGateway\Contracts\PaymentProviderInterface;
use App\Platform\PaymentGateway\DTO\PaymentRequestDTO;
use App\Platform\PaymentGateway\DTO\PaymentResultDTO;
use App\Platform\PaymentGateway\Enums\PaymentProvider;
use App\Platform\PaymentGateway\Enums\PaymentTransactionStatus;
use Illuminate\Support\Facades\Log;

/**
 * MidtransDriver — concrete PaymentProviderInterface implementation for Midtrans.
 * Requires: midtrans/midtrans-php (composer).
 * SDK dependency declared; install separately.
 */
final class MidtransDriver implements PaymentProviderInterface
{
    public function provider(): PaymentProvider { return PaymentProvider::Midtrans; }

    public function charge(PaymentRequestDTO $request): PaymentResultDTO {
        // Midtrans SDK integration point. Initial implementation returns pending.
        $orderId = $request->referenceId ?? \sprintf('DENTAL-%s-%s', $request->organizationId, now()->timestamp);
        Log::info('[MidtransDriver::charge] Payment initiated.', ['order_id' => $orderId, 'amount' => $request->amount]);
        return new PaymentResultDTO(
            success: true, status: PaymentTransactionStatus::Pending,
            referenceId: $request->referenceId, providerRef: $orderId,
            raw: ['order_id' => $orderId, 'amount' => $request->amount],
        );
    }

    public function status(string $referenceId): PaymentResultDTO {
        Log::info('[MidtransDriver::status] Checking status.', ['ref' => $referenceId]);
        return new PaymentResultDTO(success: true, status: PaymentTransactionStatus::Pending, referenceId: $referenceId);
    }

    public function refund(string $referenceId, int $amount): PaymentResultDTO {
        Log::info('[MidtransDriver::refund] Refund requested.', ['ref' => $referenceId, 'amount' => $amount]);
        return new PaymentResultDTO(success: true, status: PaymentTransactionStatus::Pending, referenceId: $referenceId);
    }

    public function handleCallback(array $payload, string $provider): PaymentResultDTO {
        $orderId = $payload['order_id'] ?? null;
        $txStatus = $payload['transaction_status'] ?? 'pending';
        $status = $this->normalizeStatus($txStatus);
        Log::info('[MidtransDriver::handleCallback] Webhook received.', ['order_id' => $orderId, 'status' => $txStatus]);
        return new PaymentResultDTO(success: true, status: $status, referenceId: $orderId, providerRef: $payload['transaction_id'] ?? null, raw: $payload);
    }

    private function normalizeStatus(string $midtransStatus): PaymentTransactionStatus {
        return match ($midtransStatus) {
            'capture','settlement'  => PaymentTransactionStatus::Paid,
            'pending'               => PaymentTransactionStatus::Pending,
            'deny','cancel','expire'=> PaymentTransactionStatus::Failed,
            'refund','partial_refund' => PaymentTransactionStatus::Refunded,
            default                 => PaymentTransactionStatus::Pending,
        };
    }
}