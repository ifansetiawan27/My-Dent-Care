<?php

declare(strict_types=1);

namespace App\Platform\PaymentGateway\Contracts;

use App\Platform\PaymentGateway\DTO\PaymentRequestDTO;
use App\Platform\PaymentGateway\DTO\PaymentResultDTO;

/**
 * PaymentGatewayServiceInterface
 *
 * The single entry point for payment processing across the ERP.
 * Routes each request to the correct provider driver, normalizes results,
 * and records activity via Audit and Logging platforms.
 *
 * Platform rule: Domains (e.g. Billing) depend on this interface only —
 * never on a specific provider SDK (Midtrans, Xendit, etc.).
 */
interface PaymentGatewayServiceInterface
{
    /**
     * Create a payment charge.
     *
     * @param  PaymentRequestDTO $request
     * @return PaymentResultDTO
     */
    public function charge(PaymentRequestDTO $request): PaymentResultDTO;

    /**
     * Query the current status of a transaction.
     *
     * @param  string $providerRef
     * @return PaymentResultDTO
     */
    public function status(string $providerRef): PaymentResultDTO;

    /**
     * Refund a transaction, fully or partially.
     *
     * @param  string   $providerRef
     * @param  int|null $amount  Amount in smallest currency unit; null for full refund.
     * @return PaymentResultDTO
     */
    public function refund(string $providerRef, ?int $amount = null): PaymentResultDTO;

    /**
     * Process a provider callback/webhook payload into a normalized result.
     *
     * @param  string               $provider  Provider code (e.g. 'midtrans').
     * @param  array<string, mixed> $payload   Raw callback payload.
     * @return PaymentResultDTO
     */
    public function handleCallback(string $provider, array $payload): PaymentResultDTO;
}
