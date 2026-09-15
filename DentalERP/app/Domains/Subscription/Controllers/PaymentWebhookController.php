<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Controllers;
use App\Domains\Subscription\Services\IdempotencyService;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use App\Domains\Subscription\Services\MidtransDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

final class PaymentWebhookController extends Controller
{
    public function __construct(
        private SubscriptionTransitionService $transitionService,
        private IdempotencyService $idempotencyService,
        private MidtransDriver $driver,
    ) {}

    public function midtrans(Request $request): JsonResponse {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        $eventId = $payload['transaction_id'] ?? $payload['order_id'];

        if (!$orderId || !$eventId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payload.'], 400);
        }

        // Verify the webhook signature BEFORE trusting anything in the payload.
        // Midtrans signs notifications with sha512(order_id + status_code +
        // gross_amount + server_key). Without this check anyone could POST a
        // forged "settlement" and activate a subscription for free.
        $serverKey = config('services.midtrans.server_key');
        if (!is_string($serverKey) || $serverKey === '') {
            Log::error('[PaymentWebhook] MIDTRANS_SERVER_KEY is not configured; rejecting webhook.', ['order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Payment gateway is not configured.'], 500);
        }

        $expectedSignature = hash('sha512', implode('', [
            (string) ($payload['order_id'] ?? ''),
            (string) ($payload['status_code'] ?? ''),
            (string) ($payload['gross_amount'] ?? ''),
            $serverKey,
        ]));

        $providedSignature = (string) ($payload['signature_key'] ?? '');
        if ($providedSignature === '' || !hash_equals($expectedSignature, $providedSignature)) {
            Log::warning('[PaymentWebhook] Invalid webhook signature rejected.', ['order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 403);
        }

        $idempotencyKey = $this->idempotencyService->webhookKey('midtrans', $eventId);
        if ($this->idempotencyService->isProcessed($idempotencyKey)) {
            Log::info('[PaymentWebhook] Duplicate webhook ignored.', ['order_id' => $orderId]);
            return response()->json(['status' => 'ok']);
        }

        $result = $this->driver->handleCallback($payload);

        Log::info('[PaymentWebhook] Processed.', [
            'order_id' => $orderId, 'status' => $result->status->value,
            'success' => $result->success, 'idempotency_key' => $idempotencyKey,
        ]);

        return response()->json(['status' => 'ok']);
    }
}