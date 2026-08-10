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

        $idempotencyKey = $this->idempotencyService->webhookKey('midtrans', $eventId);
        if ($this->idempotencyService->isProcessed($idempotencyKey)) {
            Log::info('[PaymentWebhook] Duplicate webhook ignored.', ['order_id' => $orderId]);
            return response()->json(['status' => 'ok']);
        }

        $result = $this->driver->handleCallback($payload, 'midtrans');

        Log::info('[PaymentWebhook] Processed.', [
            'order_id' => $orderId, 'status' => $result->status->value,
            'success' => $result->success, 'idempotency_key' => $idempotencyKey,
        ]);

        return response()->json(['status' => 'ok']);
    }
}