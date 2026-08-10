<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Jobs;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\IdempotencyService;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ProcessSubscriptionRenewals implements ShouldQueue
{
    use Queueable;
    public function handle(SubscriptionTransitionService $ts, IdempotencyService $idem): void {
        $due = Subscription::where('status', SubscriptionStatus::Active)->where('next_billing_at', '<=', now())->get();
        foreach ($due as $sub) {
            $key = $idem->jobKey('ProcessSubscriptionRenewals', $sub->id, $sub->next_billing_at->format('Y-m-d'));
            if ($idem->isProcessed($key)) continue;
            // Payment attempt deferred to STEP_28_35 (MidtransDriver). For now, transition to PAST_DUE.
            $ts->transition($sub, SubscriptionStatus::PastDue, SubscriptionTrigger::RenewalPaymentFailed, 'system', null, [], $key);
            Log::info('[ProcessSubscriptionRenewals] Renewal payment failed.', ['subscription_id' => $sub->id]);
        }
    }
}